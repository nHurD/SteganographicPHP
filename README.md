# Steganographic Library for PHP
This is a basic steganographis library written for PHP. In its current form, the code will store the message in the red component of each pixel.

## Requirements
This class makes extensive use of GD, so make sure that the GD extensions for PHP are installed and enabled.

## Limitations
The only format supported thus far is PNG. More work needs to be done to support other, more popular formats.

## TODO
* Basic encryption
* Find a better method for determining if a message is hidden inside an image. Currently, I store this information in the 1st pixel of the image
* Generate alternative methods for storing the data.
* Add support for JPEG images
