/*
 * sending signal from kernel module to user space. Usage:
 */

#include <linux/init.h>
#include <linux/module.h>
#include <linux/kernel.h>
#include <linux/fs.h>
#include <asm/uaccess.h>

#include <linux/sched.h>
#include <asm/siginfo.h>
#include <linux/pid_namespace.h>
#include <linux/pid.h>
#include <linux/signal.h>

MODULE_LICENSE("GPL");
MODULE_AUTHOR("Alex Xie");
MODULE_DESCRIPTION("A Linux module that sends signal to userspace.");
MODULE_VERSION("0.01");

#define DEVICE_NAME "fasync_example"
#define EXAMPLE_MSG "Hello, World!\n"


/* Prototypes for device functions */
static int device_open(struct inode *, struct file *);
static int device_release(struct inode *, struct file *);
static ssize_t device_read(struct file *, char *, size_t, loff_t *);
static ssize_t device_write(struct file *, const char *, size_t, loff_t *);

static int device_fasync(int fd, struct file *filp, int mode);


static int major_num;
static int device_open_count = 0;
static struct fasync_struct *my_fasync;


/* This structure points to all of the device functions */
static struct file_operations file_ops = {
        .read = device_read,
        .write = device_write,
        .open = device_open,
        .release = device_release,
        .fasync = device_fasync,
};


static int device_fasync(int fd, struct file *filp, int mode)
{
    printk(KERN_INFO"fasync_example: connect async\n");
    return fasync_helper(fd, filp, mode, &my_fasync);
}

/* When a process reads from our device, this gets called. */
static ssize_t device_read(struct file *flip, char *buffer, size_t len, loff_t *offset) {
        /* not implemented yet */
        return 0;
}

/* Called when a process tries to write to our device */
static ssize_t device_write(struct file *flip, const char *buffer, size_t len, loff_t *offset) {
        printk(KERN_ALERT "fasync_example: send signal to user space.\n");
    kill_fasync (&my_fasync, SIGIO, POLL_IN);
        return len;
}

/* Called when a process opens our device */
static int device_open(struct inode *inode, struct file *file) {
        /* If device is open, return busy */
    printk(KERN_INFO"fasync_example:%s,%d\n",__func__,device_open_count);
        device_open_count++;
        try_module_get(THIS_MODULE);
        return 0;
}

/* Called when a process closes our device */
static int device_release(struct inode *inode, struct file *file) {
        /* Decrement the open counter and usage count. Without this, the module would not unload. */
        device_open_count--;
        module_put(THIS_MODULE);
        return 0;
}

static int __init signal_example_init(void) {

        /* Try to register character device */
        major_num = register_chrdev(0, "fasync_example", &file_ops);
        if (major_num < 0) {
                printk(KERN_ALERT "fasync_example:Could not register device: %d\n", major_num);
                return major_num;
        } else {
                printk(KERN_INFO "fasync_example:module loaded with device major number %d\n", major_num);
                return 0;
        }
}


       
static void __exit signal_example_exit(void) {
        /* Remember — we have to clean up after ourselves. Unregister the character device. */
        unregister_chrdev(major_num, DEVICE_NAME);
        printk(KERN_INFO "signal_example:Goodbye, World!\n");
}

/* Register module functions */
module_init(signal_example_init);
module_exit(signal_example_exit);