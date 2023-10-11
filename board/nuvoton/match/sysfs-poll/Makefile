obj-m := sysfs-poll.o
 
#KDIR = /lib/modules/$(shell uname -r)/build
#KDIR ?= /lib/modules/$(shell uname -r)/build
KDIR := /home/pjeutr/nuvoton/matchOnNuc/output/build/linux-master/

all:
	$(MAKE) -C $(KDIR)  M=$(shell pwd) modules
 
clean:
	$(MAKE) -C $(KDIR)  M=$(shell pwd) clean

