/***************************************************************************//**
*  \file       epoll_userspace.c
*
*  \details    epoll user space application
*
*  \author     Peter Sloots
*
* *******************************************************************************/

#include <assert.h>
#include <fcntl.h>
#include <sys/epoll.h>
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>

#define EPOLL_SIZE ( 256 )
#define MAX_EVENTS (  20 )
#define EPOLL_VERSION "v1.2"

int main(int argc, char* argv[])
{
  char *filename;

  if (argc != 2) {
    printf("%s Usage: epoll_userspace /dev/wiegand\n", EPOLL_VERSION);
    filename = "/dev/wiegand";
  } else {
    filename = argv[1];
  }

  char kernel_val[20];
  int fd, epoll_fd, ret, n;
  struct epoll_event ev,events[20];
  
  fd = open(filename, O_RDWR | O_NONBLOCK);

  if( fd == -1 )  
  {
    perror("open /dev/wiegand");
    exit(EXIT_FAILURE);
  }

  //Create epoll instance
  epoll_fd = epoll_create(EPOLL_SIZE);
  
  if( epoll_fd < 0 )  
  {
    perror("epoll_create");
    exit(EXIT_FAILURE);
  }
    
  ev.data.fd = fd;
  //ev.events  = ( EPOLLIN | EPOLLOUT );
  ev.events  = ( EPOLLIN | EPOLLET ); //POLLET=EdgeTrigger, fixte het 
  
  //Add the fd to the epoll
  if( epoll_ctl( epoll_fd, EPOLL_CTL_ADD, fd, &ev ) )
  {
    perror("Failed to add file descriptor to epoll\n");
    close(epoll_fd);
    exit(EXIT_FAILURE);
  }

  while( 1 ) 
  {
    //Print which device is being watched  
    printf("%s MODIFY from epoll_userspace %s\n", filename, EPOLL_VERSION);

    ret = epoll_wait( epoll_fd, events, MAX_EVENTS, -1);   //wait for ever
  
    if( ret < 0 ) 
    {
      perror("epoll_wait");
      close(epoll_fd);
      assert(0);
    }
    
    for( n=0; n<ret; n++ )
    {    
      //printf("n ret=%d\n", ret);
      //printf("%d events %d\n", EPOLLIN, events[n].events);
      if( ( events[n].events & EPOLLIN )  == EPOLLIN )
      {
        read(events[n].data.fd, &kernel_val, sizeof(kernel_val));
        printf("EPOLLIN : Kernel_val = %s\n", kernel_val);
        fflush(stdout);
      }
      
      if( ( events[n].events & EPOLLOUT )  == EPOLLOUT )
      {
        strcpy( kernel_val, "User Space");
        write(events[n].data.fd, &kernel_val, strlen(kernel_val));
        printf("EPOLLOUT : Kernel_val = %s\n", kernel_val);
      }
    }
  }
  
  if(close(epoll_fd))
  {
    perror("Failed to close epoll file descriptor\n");
    return 1;
  }
  
  if(close(fd))
  {
    perror("Failed to close file descriptor\n");
    return 1;
  }
  
  return 0;
}
