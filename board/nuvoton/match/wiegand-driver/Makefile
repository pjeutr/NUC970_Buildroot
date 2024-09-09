#TODO how to get path to kernel LINUX_DIR is not resolving
KERNELDIR := /home/pjeutr/match/output/build/linux-master/
CURREN_PATH := $(shell pwd)
ccflags-y=-g
#CC=$(CROSS_COMPILE)gnueabi-gcc

obj-m := wiegand-driver.o

build:kernel_modules


kernel_modules:
	$(MAKE) -C $(KERNELDIR) M=$(CURREN_PATH) modules

clean:
	$(MAKE) -C $(KERNELDIR) M=$(CURREN_PATH) clean
	  

