#include <linux/module.h>
#include <linux/kernel.h>
#include <asm/io.h>
#include <linux/cdev.h>
#include <linux/device.h>
#include <linux/init.h>
#include <linux/fs.h>
#include <linux/uaccess.h>
#include <linux/gpio.h>
#include <linux/interrupt.h>
#include <linux/sched.h>
#include <linux/types.h>
#include <linux/delay.h>
#include <linux/irq.h>
#include <linux/poll.h>
#include <linux/gpio.h>
#include <mach/gpio.h>

#define WG_CMD_MAX_NR 		7 
#define WG_CMD_MAGIC 		'x'
#define WG_26_MODE			_IO(WG_CMD_MAGIC, 0x01)
#define WG_34_MODE			_IO(WG_CMD_MAGIC, 0x02)
#define WG_66_MODE			_IO(WG_CMD_MAGIC, 0x03)
#define WG_UNKNOWN_MODE 	_IO(WG_CMD_MAGIC, 0x07)
#define DEV_NAME "wiegand"
#define SYSFS_NAME "wiegand"

#define RD1_D1_PIN     NUC980_PA1   //reader1 d1 input
#define RD1_D0_PIN     NUC980_PA0   //reader1 d0 input
// #define RD1_GLED_PIN   NUC980_PA2   //reader1 gled output
// #define RD1_RLED_PIN   NUC980_PA3   //reader1 rled output

#define RD2_D1_PIN     NUC980_PA8   //reader2 d1 input
#define RD2_D0_PIN     NUC980_PA9   //reader2 d0 input
// #define RD2_GLED_PIN   NUC980_PA10  //reader2 gled output
// #define RD2_RLED_PIN   NUC980_PA11  //reader2 rled output
#define OUT12V_PIN     NUC980_PE10  //Match2 output 12v control output
//#define OUT12V_PIN     NUC980_PC15  //Match4 output 12v control output

static void recieve_data_convert(void);

static int major;
static int counter=0;
static int last_reader_nr=0;
static dev_t devid;
static struct cdev wiegand_cdev;
static struct class *cls;
static unsigned char wiegand_buffer[66];  
static int bit_count;    //Global Bit Counter
static unsigned long keycode;
static DECLARE_WAIT_QUEUE_HEAD (read_waitq); // Define the head of the read waiting queue
static int convert_finish_flag = 0;
static struct timer_list refresh_timer;
static int flag_timeout = 0;
static int flag_recieve_mode;
static int reader_nr = 0;

struct wiegand_io  {
	int pin_num;
	char *name;
	int flag_input;	// if input mode, flag set to 1
};

static struct wiegand_io wiegand_set[] = {
	{RD1_D1_PIN, "WGN1_IN_D1", 1},
	{RD1_D0_PIN, "WGN1_IN_D0", 1},
	{RD2_D1_PIN, "WGN2_IN_D1", 1},
	{RD2_D0_PIN, "WGN2_IN_D0", 1},
};

static void print_debug(unsigned long *data)
{
	printk("nr=%.5d keycode=%lu reader=%d\n",counter, keycode, last_reader_nr);
}

static unsigned char wiegand_26_to_keycode(unsigned long *data)
{
	int i,even,odd,hid,pid;

	// Even parity  
	even = 0;    	
	for(i = 1; i < 13;i++)    	{
		if(wiegand_buffer[i] == 1)		 
			even = (~even) & 0x01;  
	}
	
	if(even != wiegand_buffer[0]){
		bit_count = 0;	
		printk("start parity error\n");
		goto error;      	
	}		

	// Odd parity    	 
	odd = 1;    	
	for(i = 13; i< 25;i++) {	    
		if(wiegand_buffer[i] == 1)		
			odd = (~odd)& 0x01;	          
	}   

	if(odd != wiegand_buffer[25]) {	
		bit_count = 0;	
		printk("end parity error\n");
		goto error;     	 
	}	

	// Parity check passed	
	// hid conversion
	hid = 0;	
	for(i = 1 ;i<=8;i++){
		hid  |= (0x01 & wiegand_buffer[i]) << (8-i);
	}

	// pid conversion
	pid = 0;	
	for(i = 9 ;i<25;i++){
		pid |= (0x01 & wiegand_buffer[i]) << (25-i-1);
	}

	bit_count = 0;	

	*data = (hid << 16) | (pid);

	last_reader_nr = reader_nr;

	print_debug(data);

	//enable readers
	reader_nr = 0;

	return 0;
error:	
	printk("wiegand_26 Parity Error!\n");	
	return -1;

}

static unsigned char wiegand_unkown_to_keycode(unsigned long *data)
{
	int i, pid;
	last_reader_nr = reader_nr;

	// pid conversion
	pid = 0;	

	printk("%dbits\n",bit_count);
	for(i = 1; i<bit_count-1; i++){
		//
		pid |= (0x01 & wiegand_buffer[i]) << (bit_count-i-2);
	}
	bit_count = 0;	

	*data = pid;


	print_debug(data);

	//enable readers
	reader_nr = 0;

	return 0;
}

/* Timer interrupt service routine
  * Main function: process data, complete Wiegand data conversion
  * And clear the count value.
  */
static void refresh_timer_function(unsigned long data)
{
	flag_timeout = 1;
	counter++;
	if(bit_count== 26){
		flag_recieve_mode = WG_26_MODE;
	// }else if(bit_count== 34){
	// 	flag_recive_mode = WG_34_MODE;
	// }else if(bit_count== 66){
	// 	flag_recive_mode = WG_66_MODE;
	}else{
		flag_recieve_mode = WG_UNKNOWN_MODE;
	}
	recieve_data_convert();
	bit_count = 0;	
}

/*   Process the received data and complete the conversion
  * And wake up from the queue to read and wait for the sleep process.
  */
static void recieve_data_convert(void)
{
	switch(flag_recieve_mode){
	case WG_26_MODE:
		printk("\nWiegand 26 bits with parity \n");
		wiegand_26_to_keycode(&keycode);
		convert_finish_flag = 1;
		wake_up_interruptible(&read_waitq);  
		break;
	// case WG_34_MODE:
	// 	//printk("WG_34_MODE\n");
	// 	wiegand_34_to_keycode(&keycode);
	// 	convert_finish_flag = 1;
	// 	wake_up_interruptible(&read_waitq); 
	// 	break;		
	// case WG_66_MODE:
	// 	//printk("WG_66_MODE\n");
	// 	wiegand_66_to_keycode(&keycode_66);
	// 	convert_finish_flag = 1;
	// 	wake_up_interruptible(&read_waitq); 
	// 	break;
	case WG_UNKNOWN_MODE:
		// no parity check, just convert to get a number
		printk("\nWiegand skip parity - ");
		wiegand_unkown_to_keycode(&keycode);
		convert_finish_flag = 1;
		wake_up_interruptible(&read_waitq);  
		break;
	}
}

/* 
  * Input interrupt function of Wiegand input 1 data 1 
  */
static irqreturn_t wiegand_irq0(int irq, void *dev_id) //   data 1
{
	//disable other reader
	if(reader_nr == 2) {
		return IRQ_HANDLED;
	}

	disable_irq_nosync(gpio_to_irq(wiegand_set[0].pin_num));

	if(flag_timeout){
		flag_timeout = 0;
	}
	printk("1");
	reader_nr = 1;
	wiegand_buffer[bit_count] = 1;
	bit_count++;
	enable_irq(gpio_to_irq(wiegand_set[0].pin_num));
	mod_timer(&refresh_timer, jiffies+HZ/4);  // 250ms

	return IRQ_HANDLED;
}

/* 
  * Input interrupt function of Wiegand input 1 data 0 
  */
static irqreturn_t wiegand_irq1(int irq, void *dev_id)  // data 0
{
	//disable other reader
	if(reader_nr == 2) {
		return IRQ_HANDLED;
	}

	disable_irq_nosync(gpio_to_irq(wiegand_set[1].pin_num));

	if(flag_timeout){
		flag_timeout = 0;
	}
	printk("0");
	reader_nr = 1;
	wiegand_buffer[bit_count] = 0;
	bit_count++;
	enable_irq(gpio_to_irq(wiegand_set[1].pin_num));
	mod_timer(&refresh_timer, jiffies+HZ/4);  // 250ms
	return IRQ_HANDLED;
}

/* 
  * Input interrupt function of Wiegand input 2 data 1 
  */
static irqreturn_t wiegand_irq2(int irq, void *dev_id)  // data 1
{
	//disable other reader
	if(reader_nr == 1) {
		return IRQ_HANDLED;
	}

	disable_irq_nosync(gpio_to_irq(wiegand_set[2].pin_num));
	
	if(flag_timeout){
		flag_timeout = 0;
	}
	printk("1");
	reader_nr = 2;
	wiegand_buffer[bit_count] = 1;
	bit_count++;
	enable_irq(gpio_to_irq(wiegand_set[2].pin_num));
	mod_timer(&refresh_timer, jiffies+HZ/4);  // 250ms
	return IRQ_HANDLED;
}

/* 
  * Input interrupt function of Wiegand input 2 data 0 
  */
static irqreturn_t wiegand_irq3(int irq, void *dev_id)  // data 0
{
	//disable other reader
	if(reader_nr == 1) {
		return IRQ_HANDLED;
	}

	disable_irq_nosync(gpio_to_irq(wiegand_set[3].pin_num));

	if(flag_timeout){
		flag_timeout = 0;
	}
	printk("0");
	reader_nr = 2;
	wiegand_buffer[bit_count] = 0;
	bit_count++;
	enable_irq(gpio_to_irq(wiegand_set[3].pin_num));
	mod_timer(&refresh_timer, jiffies+HZ/4);  // 250ms
	return IRQ_HANDLED;
}

static int wiegand_release(struct inode *inode, struct file *file) 
{
	printk (KERN_INFO "wiegand_release ok.\n");
	return 0;
}

static int wiegand_open(struct inode *inode, struct file *file)
{
	printk(KERN_INFO "wiegand_open ok.\n");
	return 0;
}

/* 
  * Wiegand read function, when there is no data, the upper application passes read(fd, buf, size)
  * Indirect calls will cause this function to sleep, and it will not wake up and return from the queue until there is data.
  */
ssize_t wiegand_read(struct file *file, char __user *buf, size_t size, loff_t *ppos)
{
	int err;
	//enable_irq(gpio_to_irq(wiegand_set[0].pin_num));
	//enable_irq(gpio_to_irq(wiegand_set[1].pin_num));
	printk("wiegand_read called.\n");

	wait_event_interruptible (read_waitq, convert_finish_flag); // Data has not been converted, please wait here
	convert_finish_flag = 0;

	err = copy_to_user(buf, &keycode, sizeof(unsigned long));
	if(err){
		printk("%s copy_to_user error(%d)\n", __func__, err);
		return -1;
	}

	bit_count = 0;
	//keycode= 0;

	//disable_irq_nosync(gpio_to_irq(wiegand_set[0].pin_num));
	//disable_irq_nosync(gpio_to_irq(wiegand_set[1].pin_num));
	return 0;
}	

/* Upper-level applications can query data through the poll mechanism
  * Avoid unnecessary sleep waiting
  */
static unsigned wiegand_poll(struct file *file, poll_table *wait)
{
	unsigned int mask = 0;

	poll_wait (file, &read_waitq, wait ); // Data is not readable and will not sleep immediately

	if (convert_finish_flag)
		mask |= POLLIN | POLLRDNORM;

	return mask;
}

/* returns the in sysfs, /sys/kernel/wiegand/read */
static ssize_t wiegand_show(
  struct kobject *kobj, struct kobj_attribute *attr, char *buf)
{
  return sprintf(
    buf, "%.5d:%lu:%d:%s\n",
    counter,
    keycode,
    last_reader_nr,
    wiegand_buffer
  );
}

static struct kobj_attribute wiegand_attribute =
  __ATTR(read, 0444, wiegand_show, NULL);
static struct kobj_attribute wiegand2_attribute =
  __ATTR(read2, 0660, wiegand_show, NULL);
static struct attribute *attrs[] =
{
  &wiegand_attribute.attr,
  &wiegand2_attribute.attr,
  NULL,   /* need to NULL terminate the list of attributes */
};
static struct attribute_group attr_group =
{
  .attrs = attrs,
};
static struct kobject *wiegandKObj;
//NEW//

static struct file_operations wiegand_fops = {
	.owner = THIS_MODULE,
	.open  = wiegand_open,
	.release  = wiegand_release,
	.read = wiegand_read,
	.poll = wiegand_poll,
};

static int __init wiegand_init(void)
{
	//create character device
	int err, i, retval;

	if (major) {
		devid = MKDEV(major, 0);
		register_chrdev_region(devid, 1, DEV_NAME);  
	} else {
		alloc_chrdev_region(&devid, 0, 1, DEV_NAME); 
		major = MAJOR(devid);                     
	}
	
	cdev_init(&wiegand_cdev, &wiegand_fops);
	cdev_add(&wiegand_cdev, devid, 1);

	cls = class_create(THIS_MODULE, DEV_NAME);
	device_create(cls, NULL, devid, NULL, DEV_NAME); 	/* /dev/wiegand */

	//Remove power switch, do it form inputListener.php
	//Activate wiegand power
	// ret = gpio_request( OUT12V_PIN ,"OUT12V_PIN");
	// if(ret < 0)
	// {
	//   printk(KERN_EMERG "GPIO REQUEST OUT12V_PIN FAILED!\n");
	// }
	// else
	// {
	//   gpio_direction_output(OUT12V_PIN,0);  //output 12v  control set output and value low 
	//   gpio_set_value( OUT12V_PIN ,1);  //open reader power
	// }

	//sleep, let the poweron become stable
	ssleep(2);

	init_timer(&refresh_timer);
	refresh_timer.function = refresh_timer_function;
	add_timer(&refresh_timer);

	//initialize gpio inputs
	for(i = 0; i < ARRAY_SIZE(wiegand_set); i++){
		err = gpio_request(wiegand_set[i].pin_num,wiegand_set[i].name);
		if(err){
			printk("Cannot Request the gpio of  %d\n", wiegand_set[i].pin_num);
			goto out;
		}
		err = gpio_direction_input(wiegand_set[i].pin_num);
		if (err < 0) {
			printk("Cannot set the gpio to input mode. \n");
			gpio_free(wiegand_set[i].pin_num);
			goto out;
		}
	}

	//create irq for Reader1
	err = request_irq(gpio_to_irq(wiegand_set[0].pin_num), wiegand_irq0,
			IRQF_TRIGGER_FALLING, "WIEGAND1_IN_D0", &wiegand_set[0]);
	if(err){
		printk("%s:%d request IRQ(%d),ret:%d failed!\n",__func__,__LINE__, gpio_to_irq(wiegand_set[0].pin_num),err);
		goto out;
	}

	err = request_irq(gpio_to_irq(wiegand_set[1].pin_num), wiegand_irq1,
			IRQF_TRIGGER_FALLING, "WIEGAND1_IN_D1", &wiegand_set[1]);
	if(err){
		printk("%s:%d request IRQ(%d),ret:%d failed!\n",__func__, __LINE__, gpio_to_irq(wiegand_set[1].pin_num),err);
		free_irq(gpio_to_irq(wiegand_set[0].pin_num), &wiegand_set[0]);
		goto out;
	}

	//create irq for Reader2
	err = request_irq(gpio_to_irq(wiegand_set[2].pin_num), wiegand_irq2,
			IRQF_TRIGGER_FALLING, "WIEGAND2_IN_D0", &wiegand_set[2]);
	if(err){
		printk("%s:%d request IRQ(%d),ret:%d failed!\n",__func__,__LINE__, gpio_to_irq(wiegand_set[2].pin_num),err);
		goto out;
	}

	err = request_irq(gpio_to_irq(wiegand_set[3].pin_num), wiegand_irq3,
			IRQF_TRIGGER_FALLING, "WIEGAND2_IN_D1", &wiegand_set[3]);
	if(err){
		printk("%s:%d request IRQ(%d),ret:%d failed!\n",__func__, __LINE__, gpio_to_irq(wiegand_set[3].pin_num),err);
		free_irq(gpio_to_irq(wiegand_set[2].pin_num), &wiegand_set[2]);
		goto out;
	}

	//setup the sysfs, /sys/kernel/wiegand/read 
	wiegandKObj = kobject_create_and_add(SYSFS_NAME, kernel_kobj);

	if (!wiegandKObj) {
		printk("wiegand failed to create sysfs\n");
		return -ENOMEM;
	}

	retval = sysfs_create_group(wiegandKObj, &attr_group);
	if (retval) {
		kobject_put(wiegandKObj);
	}

	return 0;
out:
	while(i--){
		gpio_free(wiegand_set[i].pin_num);
	}
	return -1;
	
}

static void __exit wiegand_exit(void)
{
	int i; 
	printk("wiegand_exit called.\n");
	kobject_put(wiegandKObj);
	del_timer (& refresh_timer);

	for(i = 0; i < ARRAY_SIZE(wiegand_set); i++){
		if(wiegand_set[i].flag_input){
			free_irq(gpio_to_irq(wiegand_set[i].pin_num), &wiegand_set[i]);
		}
		gpio_free(wiegand_set[i].pin_num);
	}
	
	device_destroy(cls, devid);
	class_destroy(cls);

	cdev_del(&wiegand_cdev);
	unregister_chrdev_region(devid, 1);
}

module_init(wiegand_init);
module_exit(wiegand_exit);


MODULE_AUTHOR ("Maasland");
MODULE_DESCRIPTION("Wiegand driver");
MODULE_LICENSE("GPL");
