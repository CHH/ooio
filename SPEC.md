# IO

An object oriented interface to PHP's streams.

## Classes & Interfaces

### Interface IO\Writeable

 * `write(string $data)`:  
   Writes the given string to the stream and returns the number of Bytes
   which got written.

### Interface IO\Readable

 * `read([int $length = null])`:  
   If `length` is omitted or Null then it reads everything up to EOF into 
   a String.

### Interface IO\Seekable

 * `seek([int $offset = 0, [int $whence = SEEK_SET]])`

### Interface IO\Rewindable 

 * `rewind()`

### Interface IO\Flushable

 * `flush()`

### Interface IO\Closable

 * `close()`

### Interface IO\FileDescriptor

 * `toFileDescriptor()`: Must return a stream resource for use in stream
   functions.

### Class IO\Stream

 * Implements IO interfaces with standard PHP Stream functions, like
   `fwrite`, `fread`, `fgets`, `fseek`, `fclose`.
 * Returned by `IO\open`, `IO\pipe`, `IO\Socket\{client,server}`,… functions.
 * Could implement additional utility functions like `gets` (alias for `fgets`), and `puts` (writes data with a separator).
 * `getIterator` should return an iterator which iterates over each 
   line of the stream.
 * Should provide a method for getting an iterator which iterates over each byte
   of the stream.

### Class IO\StreamContext

 * Allows to pass options to the given wrappers.
 * Has an `open` method, which acts just like `IO\open` but constructs and passes
   the stream context to `fopen`.
 * Implements the same functions like the core `IO` namespace with the same
   interface, except that they act within the stream context.

Example:

	<?php
	
	use OOIO\StreamContext;
	
	$ctx = new StreamContext([
		"http" => [
			"method" => "HEAD"
		]
	]);
	
	# To get an IO object:
	$stream = $ctx->open("http://google.com");
	
	# To get the contents only (same like `IO\read()` except it
	# respects the stream context options):
	$body = $ctx->read("http://google.com");

## Functions

These are all namespaced functions which act in the default stream context.
Note that `read` and `write` are both _binary safe_.

### read(string $name, [int $maxLength = -1, [int $offset = -1]])

Acts like `file_get_contents`.

### write(string $name, string $data)

Acts like `file_put_contents`.

Example:

	<?php
	
	use OOIO\IO;
	
	IO::write("/tmp/lol.txt", str_repeat(3, "lol "));

### open(string $uri, [string $mode])

Opens a stream, much like `fopen` but returns an `Jack\IO\Stream`.

Example:

	<?php
	
	use OOIO\IO;
	
	$stream = IO::open("/tmp/foo.txt", "w+");
	$stream->puts("Foo Bar");

### select()

Returns a selector which must be initialized with streams.

When the stream is set to non-blocking it returns immediately, otherwise
it blocks until at least one file descriptor changed or the timeout
is hit.

Example:

	<?php
	
	use OOIO\IO;
	
    $selector = IO::select();
    $selector->register(IO::stdin(), 'r');	

    list($r, $w, $e) = $selector->select();

	# The item exists in the response, so there's something there
	# to be read. Let's read it:
	if ($res = @$r[0]) {
		echo "Got: ", $res->read();
	}

### pipe()

Returns a list of two interconnected Unix streams.

What gets written to one side is available for reading on the other.

Example:

	<?php
	
	use OOIO\IO;
	
	$msg = "ping";
	list($rp, $wp) = IO::pipe();

    $selector = IO::select()->register($rp, 'r')->register($wp, 'w');
	
	foreach (range(0, 100) as $_) {
		list($r, $w) = $selector->select();
		
		if ($r) {
			$ret = $r[0]->gets();
			
			print($ret.PHP_EOL);
			
			switch ($ret) {
				case "ping":
					$msg = "pong";
					break;
				default:
				case "pong":
					$msg = "ping";
					break;
			}
		}
		
		if ($w) {
			$w[0]->puts($msg);
		}
	}

# IO\Socket

## Functions

### server(string $socketName)

Example:

	<?php
	
	use OOIO\IO, 
	    OOIO\IO\Socket;
	
    $STDERR   = IO::stderr();
	$server   = Socket::server("tcp://127.0.0.1");
    $selector = IO::select()->register($server, 'r');
	
	for (;;) {
		list($readable) = $selector->select();
		
		if ($readable) {
			$peer = $readable[0]->accept(0);
			
			if (!$peer) continue;
			
			$msg = $peer->gets();
			
			if (preg_match("/quit/i", $msg)) {
				$peer->close();
			} else {
				$STDERR->printf("Got: %s\n", [$msg]);
			
				$peer->puts($msg);
			}
		}
	}

### client(string $socketName)
