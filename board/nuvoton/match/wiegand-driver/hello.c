#include <linux/module.h>
#include <linux/kernel.h>
#include <linux/init.h>
#include <linux/fs.h>
#include <linux/slab.h>

struct my_attr {
    struct attribute attr;
    int value;
};

static struct my_attr notify = {
    .attr.name="notify",
    .attr.mode = 0644,
    .value = 0,
};

static struct my_attr trigger = {
    .attr.name="trigger",
    .attr.mode = 0644,
    .value = 0,
};

static struct attribute * myattr[] = {
    &notify.attr,
    &trigger.attr,
    NULL
};

static ssize_t show(struct kobject *kobj, struct attribute *attr, char *buf)
{
    struct my_attr *a = container_of(attr, struct my_attr, attr);
    printk( "hello: show called (%s)\n", a->attr.name );
    return scnprintf(buf, PAGE_SIZE, "%s: %d\n", a->attr.name, a->value);
}
static struct kobject *mykobj;

static ssize_t store(struct kobject *kobj, struct attribute *attr, const char *buf, size_t len)
{
    struct my_attr *a = container_of(attr, struct my_attr, attr);

    sscanf(buf, "%d", &a->value);
    notify.value = a->value;
    printk("sysfs_notify store %s = %d\n", a->attr.name, a->value);
    sysfs_notify(mykobj, NULL, "notify");
    return sizeof(int);
}

static struct sysfs_ops myops = {
    .show = show,
    .store = store,
};

static struct kobj_type mytype = {
    .sysfs_ops = &myops,
    .default_attrs = myattr,
};

static struct kobject *mykobj;
static int __init hello_module_init(void)
{
    int err = -1;
    printk("Hello: init\n");
    mykobj = kzalloc(sizeof(*mykobj), GFP_KERNEL);
    if (mykobj) {
        kobject_init(mykobj, &mytype);
        if (kobject_add(mykobj, NULL, "%s", "hello")) {
             err = -1;
             printk("Hello: kobject_add() failed\n");
             kobject_put(mykobj);
             mykobj = NULL;
        }
        err = 0;
    }
    return err;
}

static void __exit hello_module_exit(void)
{
    if (mykobj) {
        kobject_put(mykobj);
        kfree(mykobj);
    }
    printk("Hello: exit\n");
}

module_init(hello_module_init);
module_exit(hello_module_exit);
MODULE_LICENSE("GPL");