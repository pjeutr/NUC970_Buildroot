#include <stdint.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <fcntl.h>
#include <sys/types.h> 
#include <sys/stat.h> 
#include <poll.h>

#define WIEGAND_READER1  "/sys/wiegand/reader1"
#define WIEGAND_READER2   "/sys/wiegand/reader2"

int main(int argc, char **argv)
{
    int cnt, reader1_fd, reader2_fd, rv;
    char attrData[100];
    struct pollfd ufds[2];

    if ((reader1_fd = open(WIEGAND_READER1, O_RDWR)) < 0){
        perror("Unable to open reader1");
        exit(1);
    }

    if ((reader2_fd = open(WIEGAND_READER2, O_RDWR)) < 0) {
        perror("Unable to open reader2");
        exit(1);
    }

    ufds[0].fd = reader1_fd;
    ufds[0].events = POLLPRI|POLLERR;
    ufds[1].fd = reader2_fd;
    ufds[1].events = POLLPRI|POLLERR;

    cnt = read( reader1_fd, attrData, 100 );
    cnt = read( reader2_fd, attrData, 100 );
    ufds[0].revents = 0;
    ufds[1].revents = 0;

    if (( rv = poll( ufds, 2, 1000000)) < 0 ) 
        perror("poll error");
    else if (rv == 0)
        printf("Timeout occurred!\n");
    else
        printf("triggered\n");
    
    printf( "revents[0]: %08X\n", ufds[0].revents );
    printf( "revents[1]: %08X\n", ufds[1].revents );
    close( reader2_fd );
    close( reader1_fd );
}
