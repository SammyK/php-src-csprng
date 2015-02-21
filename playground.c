#include <stdio.h>
#include <sys/stat.h>
#include <fcntl.h>

#define SUCCESS 1
#define FAILURE 0
#define E_WARNING 3

// Copy/pasted from string.c
static char hexconvtab[] = "0123456789abcdef";

// Copy/pasted from string.c
static void php_bin_to_hex(char *old, const int old_len, char *hex)
{
	int i, j;

	for (i = j = 0; i < old_len; i++) {
		hex[j++] = hexconvtab[old[i] >> 4];
		hex[j++] = hexconvtab[old[i] & 15];
	}

	hex[j] = '\0';
}

// Copy/pasted from mcrypt.c
static int php_random_bytes(char *bytes, int size)
{
	int n = 0;

	int    fd;
	size_t read_bytes = 0;

	fd = open("/dev/urandom", O_RDONLY);
	if (fd < 0) {
		printf(NULL, E_WARNING, "Cannot open source device\n");
		return FAILURE;
	}
	while (read_bytes < size) {
		n = read(fd, bytes + read_bytes, size - read_bytes);
		if (n < 0) {
			break;
		}
		read_bytes += n;
	}
	n = read_bytes;
	close(fd);
	if (n < size) {
		printf("Could not gather sufficient random data\n");
		return FAILURE;
	}

	return SUCCESS;
}

void main()
{
    char rand[20];
    char hex[40];

    php_random_bytes(rand, 20);
    printf("\nHello World\n");
    printf("\n%s\n", rand);

    php_bin_to_hex(rand, 20, hex);
    printf("\n%s\n", hex);
}
