<?php
/*
***************************************************************************
** Created by Jonathan R. Steele <jrsteele@gmail.com>
** This is a very simple stegonographic class at the moment
** it does not encrypt the message nor verify if the message exceeds
** the max size of the image.
**
** The code will only store the parts of the message in the red 
** component of the pixel, but it can be easily changed to store it in
** the remaining parts to increase the message to image size ratio.
**
** Currently, this is only storing one portion of a string converted to its
** ASCII value. I'll have to look into a better storage method.
***************************************************************************
*/
class Stego {
	
	var $image;
	var $message;
	var $messageLength;
	var $height;
	var $width;
	
	
	/* Empty Constructor */
	function Stego() { }
	
	/* this will return an array of the reg, green, and blue components of an image as an array*/
	function getPixel ($x,$y) {
		/* Make sure we have an image */
		if (!$this->image) {
			trigger_error("No image is set",E_USER_WARNING);
			return false;
		}
		$color = @imagecolorat($this->image,$x,$y);
		if ($color == false) return false;
		
		/* Determine if we are using true colors or indexed colors */
		if (imageistruecolor($this->image)) {
			/* Color components */
			$red = ($color >> 16) & 255;
			$green = ($color >> 8) & 255;
			$blue = $color & 255;
			$return_value = array('red' => $red, 'green' => $green, 'blue' => $blue);
		} else {
			$return_value = imagecolorsforindex($this->image,$color);
		}
		
		return $return_value;
	}
	
	/* this will set the pixel with the color defined in the array */
	function setPixel ($x,$y,$color) {
		/* Sanity Checks */
		if (!is_array($color)) return false;
		if (!$this->image) {
			trigger_error("No image is set",E_USER_WARNING);
			return false;
		}
		$c = imagecolorallocate($this->image,$color['red'],$color['green'],$color['blue']);		
		imagesetpixel($this->image,$x,$y,$c);
		return true;
	}
	
	/* Set the image resource */
	function setImage($imageFile) {
		$this->image = imagecreatefrompng($imageFile);
		if (!$this->image) return false;	
		
		/* make extra sure we're in bounds */
		$this->height = imagesy($this->image) - 1;
		$this->width = imagesx($this->image) - 1;
		
		return true;
	}
	
	/* Free up the memory */
	function destroy() {
		imagedestroy($this->image);
	}
	
	/* 
	** Encode the message into the image
	** The first pixel we will use to determine if it's encoded and the length
	*/
	function encode_message() {
		if (!$this->image || empty($this->message)) return false;
		
		/* Ensure we do not go over the maximum size */
		if (strlen($this->message) > $this->get_max_message_size()) {
			trigger_error('The message is too large to fit inside of the image.',E_USER_ERROR);
			return false;
		}
		
		/* Get the first pixel */
		$col = $this->getPixel(0,0);
		
		/* Set our flags */
		$col['red'] = ord('E');
		$col['blue'] = ord('N');
		$col['green'] = strlen($this->message);
		$this->setPixel(0,0,$col);
		
		/* Set the message length and index */
		$this->messageLength = strlen($this->message);
		$cur_idx = 0;
		
		/* Scroll through the pixels and set our values */
		for ($y=0; $y < $this->height; $y++) {
			for ($x=1; $x < $this->width; $x++) {
				if ($cur_idx == $this->messageLength) break;
				$col = $this->getPixel($x,$y);
				$col['red'] = ord($this->message[$cur_idx++]);
				$this->setPixel($x,$y,$col);
			}
			if ($cur_idx == $this->messageLength) break;
		}
		return true;
	}
	
	/*
	** Extract the message from the image
	*/
	function extract_message() {
		/* Sanity Checks */
		if (!$this->image) return false;
		$col = $this->getPixel(0,0);
		$test = chr($col['red']) . chr($col['blue']);
		if ($test != 'EN') {
			trigger_error('Image is not encoded',E_USER_WARNING);
			return false;
		}
		/* Set the message length and index */
		$this->message = '';
		$this->messageLength = $col['green'];
		$cur_idx = 0;
		
		/* Scroll through image and get our message */
		for ($y=0; $y < $this->height; $y++) {
			for($x=1; $x < $this->width; $x++) {
				$col = $this->getPixel($x,$y);
				if ($cur_idx == $this->messageLength) break;
				$this->message .= chr($col['red']);
				$cur_idx++;
			}
			if ($cur_idx == $this->messageLength) break;
		}
		return true;
	}
	
	/* Output the image */
	function display() {
		header('content-type: image/png');
		header("Pragma: no-cache");
		imagepng($this->image);
	}
	
	/* Save the image to a file */
	function save($filename) {
		imagepng($this->image,$filename);
	}
	
	/* 
	** Calculate the storage capacity for the image 
	** Note: we are only storing portions of the message in the red component of the pixel
	** this can be changed down the road to increase storage capacity.
	**
	** Because of the simple storage method, it is basically (width * height) - 1. We need
	** one pixel to store the message length.
	*/
	function get_max_message_size () {
		if (!is_resource($this->image)) {
			trigger_error("The specified image is not a valid resource object",E_USER_ERROR);
			return false;
		}
		/* Get the height and width */
		$this->width = imagesx($this->image) - 1;
		$this->height = imagesy($this->image) -1;
		return ($this->width * $this->height)-1;
		
		
	
	}
	



}




?>
