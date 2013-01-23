<?php

namespace OOIO;

class Exception extends \Exception
{}

class FileNotFoundException extends Exception
{}

/** Exception which gets thrown if the End of a file
 * is reached unexpectedly.
 */
class EofException extends Exception
{}

class WriteException extends Exception
{}

class ClosedException extends Exception
{}
