#include <linux/module.h>
#include <linux/kernel.h>
#include <linux/init.h>
#include <linux/fs.h>
#include <linux/slab.h>
#include <linux/delay.h>
#include <linux/kobject.h>
#include <linux/gpio.h>
#include <mach/gpio.h>

#define RD1_D1_PIN     NUC980_PA0   //reader1 d1 input
#define RD1_D0_PIN     NUC980_PA1   //reader1 d0 input
#define RD2_D1_PIN     NUC980_PA8   //reader2 d1 input
#define RD2_D0_PIN     NUC980_PA9   //reader2 d0 input
#define OUT12V_PIN     NUC980_PE10  //output 12v control output

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

static struct timer_list refresh_timer;
static unsigned long keycode;





struct d_attr {
    struct attribute attr;
    int value; /* This is our data */
};

static struct d_attr reader1 = {
    .attr.name="reader1",
    .attr.mode = 0644,
    .value = 0,
};

static struct d_attr reader2 = {
    .attr.name="reader2",
    .attr.mode = 0644,
    .value = 0,
};

static struct attribute * d_attrs[] = {
    &reader1.attr,
    &reader2.attr,
    NULL
};








static unsigned char wiegand_unkown_to_keycode(unsigned long *data)
{
	// int i, pid;
	// last_reader_nr = reader_nr;

	// // pid conversion
	// pid = 0;	

	// printk("%dbits\n",bit_count);
	// for(i = 1; i<bit_count-1; i++){
	// 	//
	// 	pid |= (0x01 & wiegand_buffer[i]) << (bit_count-i-2);
	// }
	// bit_count = 0;	

	// *data = pid;
	printk("data");
	//print_debug(data);

	//enable readers
	//reader_nr = 0;

	return 0;
}

static struct kobject *wiegandKObj;

/*   Process the received data and complete the conversion
  * And wake up from the queue to read and wait for the sleep process.
  */
static void recieve_data_convert(void)
{
	// switch(flag_recieve_mode){
	// case WG_26_MODE:
	// 	printk("\nWiegand 26 bits with parity \n");
	// 	wiegand_26_to_keycode(&keycode);
	// 	convert_finish_flag = 1;
	// 	wake_up_interruptible(&read_waitq);  
	// 	break;
	// // case WG_34_MODE:
	// // 	//printk("WG_34_MODE\n");
	// // 	wiegand_34_to_keycode(&keycode);
	// // 	convert_finish_flag = 1;
	// // 	wake_up_interruptible(&read_waitq); 
	// // 	break;		
	// // case WG_66_MODE:
	// // 	//printk("WG_66_MODE\n");
	// // 	wiegand_66_to_keycode(&keycode_66);
	// // 	convert_finish_flag = 1;
	// // 	wake_up_interruptible(&read_waitq); 
	// // 	break;
	// case WG_UNKNOWN_MODE:
		// no parity check, just convert to get a number
		printk("\nWiegand skip parity - ");
		wiegand_unkown_to_keycode(&keycode);
		//convert_finish_flag = 1;
		//wake_up_interruptible(&read_waitq);  
	// 	break;
	// }
}

/* Timer interrupt service routine
  * Main function: process data, complete Wiegand data conversion
  * And clear the count value.
  */
static void refresh_timer_function(unsigned long data)
{
	/*
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
	*/
	recieve_data_convert();
	sysfs_notify(wiegandKObj, NULL, "reader1");
	sysfs_notify(wiegandKObj, NULL, "reader2");
	printk("sysfs_notify");

	//kill_fasync (&my_fasync, SIGIO, POLL_IN);
	//wiegand_value_store(wiegandKObj, "value" "klaas", null);
	//bit_count = 0;	
}
/* 
  * Input interrupt function of Wiegand input 1 data 1 
  */
static irqreturn_t wiegand_irq0(int irq, void *dev_id) //   data 1
{
	//disable other reader
	// if(reader_nr == 2) {
	// 	return IRQ_HANDLED;
	// }

	disable_irq_nosync(gpio_to_irq(wiegand_set[0].pin_num));

	// if(flag_timeout){
	// 	flag_timeout = 0;
	// }
	reader1.value = reader1.value + 1;
	printk("1");
	//reader_nr = 1;
	//wiegand_buffer[bit_count] = 1;
	//bit_count++;
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
	// if(reader_nr == 2) {
	// 	return IRQ_HANDLED;
	// }

	disable_irq_nosync(gpio_to_irq(wiegand_set[1].pin_num));

	// if(flag_timeout){
	// 	flag_timeout = 0;
	// }
	reader1.value = reader1.value + 0;
	printk("0");
	//reader_nr = 1;
	//wiegand_buffer[bit_count] = 0;
	//bit_count++;
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
	// if(reader_nr == 1) {
	// 	return IRQ_HANDLED;
	// }

	disable_irq_nosync(gpio_to_irq(wiegand_set[2].pin_num));
	
	// if(flag_timeout){
	// 	flag_timeout = 0;
	// }
	reader2.value = reader2.value + 1;
	printk("1");
	//reader_nr = 2;
	//wiegand_buffer[bit_count] = 1;
	//bit_count++;
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
	// if(reader_nr == 1) {
	// 	return IRQ_HANDLED;
	// }

	disable_irq_nosync(gpio_to_irq(wiegand_set[3].pin_num));

	// if(flag_timeout){
	// 	flag_timeout = 0;
	// }
	reader2.value = reader2.value + 0;
	printk("0");
	//reader_nr = 2;
	//wiegand_buffer[bit_count] = 0;
	//bit_count++;
	enable_irq(gpio_to_irq(wiegand_set[3].pin_num));
	mod_timer(&refresh_timer, jiffies+HZ/4);  // 250ms
	return IRQ_HANDLED;
}










static ssize_t show(struct kobject *kobj, struct attribute *attr, char *buf)
{
    struct d_attr *da = container_of(attr, struct d_attr, attr);
    pr_info( "duo: show called (%s)\n", da->attr.name );
    return scnprintf(buf, PAGE_SIZE, "%s: %d\n", da->attr.name, da->value);
}
static struct kobject *wiegandKObj;

static ssize_t store(struct kobject *kobj, struct attribute *attr, const char *buf, size_t len)
{
    struct d_attr *da = container_of(attr, struct d_attr, attr);

    sscanf(buf, "%d", &da->value);
    pr_info("sysfs_reader1 store %s = %d\n", da->attr.name, da->value);

    if (strcmp(da->attr.name, "reader1") == 0){
        reader1.value = da->value;
        sysfs_notify(wiegandKObj, NULL, "reader1");
    }
    else if(strcmp(da->attr.name, "reader2") == 0){
        reader2.value = da->value;
        sysfs_notify(wiegandKObj, NULL, "reader2");
    }
    return sizeof(int);
}

static struct sysfs_ops s_ops = {
    .show = show,
    .store = store,
};

static struct kobj_type k_type = {
    .sysfs_ops = &s_ops,
    .default_attrs = d_attrs,
};

static struct kobject *wiegandKObj;
static int __init wiegand_init(void)
{
    int err = -1;
    int i, ret;
    pr_info("Flexess Duo module: init\n");
    wiegandKObj = kzalloc(sizeof(*wiegandKObj), GFP_KERNEL);
    /* wiegandKObj = kobject_create() is not exported */
    if (wiegandKObj) {
        kobject_init(wiegandKObj, &k_type);
        if (kobject_add(wiegandKObj, NULL, "%s", "wiegand")) {
             err = -1;
             pr_info("Wiegand: kobject_add() failed\n");
             kobject_put(wiegandKObj);
             wiegandKObj = NULL;
        }
        err = 0;
    }

	//Activate wiegand power
	ret = gpio_request( OUT12V_PIN ,"OUT12V_PIN");
	if(ret < 0)
	{
	  printk(KERN_EMERG "GPIO REQUEST OUT12V_PIN FAILED!\n");
	}
	else
	{
	  gpio_direction_output(OUT12V_PIN,0);  //output 12v  control set output and value low 
	  gpio_set_value( OUT12V_PIN ,1);  //open reader power
	}

	//sleep, let the poweron become stable
	//ssleep(2);

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

    return err;
out:
	while(i--){
		gpio_free(wiegand_set[i].pin_num);
	}
	return -1;
}

static void __exit wiegand_exit(void)
{
	int i; 

    if (wiegandKObj) {
        kobject_put(wiegandKObj);
        kfree(wiegandKObj);
    }
    pr_info("Wiegand sysfs: exit\n");	
	del_timer (& refresh_timer);

	for(i = 0; i < ARRAY_SIZE(wiegand_set); i++){
		if(wiegand_set[i].flag_input){
			free_irq(gpio_to_irq(wiegand_set[i].pin_num), &wiegand_set[i]);
		}
		gpio_free(wiegand_set[i].pin_num);
	}
}

module_init(wiegand_init);
module_exit(wiegand_exit);

MODULE_AUTHOR ("Maasland");
MODULE_DESCRIPTION("Wiegand driver");
MODULE_LICENSE("GPL");













