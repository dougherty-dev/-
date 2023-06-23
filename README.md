# Shashin

写真 (しゃしん, ja: shashin): «photograph»<br />
写真 (zh: xiězhēn): «portrait», «portray»<br />  

A minimalist, light-weight, and portable lookbook software for showcasing a personal portfolio of large images using HDPI and modern image formats.

## Requirements

* PHP 8.1 (GD with AVIF support)
* Apache 2.4
* SQLite3

## Features

No users, no config. Just upload the software and a password file to the **root folder** of your web server, and you are good to go. PHP must have **write access** to the root folder.

Define your sets, then upload your images. Images will be rendered in AVIF, WebP or legacy JPEG format. Image sizes are fixed and defined for high resolution or large screens:

* Original: 2000+ px max width or height
* Medium: 2000 px (image pages)
* Small: 1000 px (set pages)
* Thumb: 500 px (admin handling only)

Sets and images can be managed by adding titles and descriptions, or changing order of appearance. Move images between sets. Define poster images for sets.

Customize header, footer, and other information. Override default layout with your own theme. Upload additional items, like PDF files or movies, and associate with an image. Create HTML pages for additional information.

## Q & A

### What is this password file?

It is a text file named **.htpassword.txt** with the admin password given on the first line. Upload it to the Shashin software directory to complete the installation. If you forget your password, you can always upload a new password file. This is a simple and reasonably secure method without the need for e-mail.

### What image format should I use?

AVIF is **vastly** superior, as it handles up to 12 bits per channel (48 bits with transparency) and has a much better compression algorithm. However, GD, Imagick, and most other server software reduce everything to 8 bits, for which WebP is a worthy contender. AVIF is typically not available on most PHP installations. AVIF in itself is also extremely processor intensive, and may not be a feasible solution for your server.

A solution offered by this software is to manually replace images with hand-crafted versions with higher bit depth whenever needed. Typically, images with smooth gradients will benefit enormously from 12-bit AVIF, even though monitors are typically 8-bit.

A 21 Mpx image will usually be reduced to a few hundred kilobytes or less in size without artifacts using 12-bit AVIF, whereas 8-bit WebP and JPEG will require considerably more data while still displaying banding or noise.

It also depends on your source. Phone cameras typically only use 10 bits per channel, while DSLR/M cameras store 14 bits per channel in RAW format. Editing in 16 bits, and then publishing to 12 bits AVIF, is therefore the ideal flow for serious photography.

### What quality setting should I use?

There is no magic constant. Typically 30 will do for most images, while grainy gradients (like night skies) may require up to 90 to avoid banding or posterizing. Note that such images are better managed by manual GIMP export (12-bit, 50 %) or Photoshop Camera Raw export (10-bit, quality 2/10).

### How do I replace images?

If your server can’t handle AVIF or even WebP, or if you want to use high bit depth AVIF, you can upload an original file in whatever format your server supports (like JPEG), and then replace (🔄) the entire set (-o, -m, -s, -t) in AVIF or WebP in the admin interface.

However, image size or image type can’t be verified with this bypassing method – make sure you know what you are doing. Specifically, if you want to replace image.jpg, drag the complete set image-o.avif, image-m.avif, image-s.avif, image-t.avif to the replace interface.

### Can I have feature X?

This piece of software fills a specific niche purpose, and is intended to be as featureless and uncomplicated as possible. Specifically, it is not a gallery for all your millions of photos, but a portfolio for your very best images, or for a limited set of products or other items. However, it could easily be upgraded to a gallery by making the set structure nested and adding a few routines. Fork it.

### Why GD and not Imagick?

GD is bundled with PHP, whereas Imagick is a PECL extension. Imagick also has a very buggy implementation of WebP and AVIF, and it will most likely kill your VPS by OOM exceptions.

### How do I apply a theme?

Write a file theme.css and put it in a folder, optionally together with images and other items. Zip the folder, and upload it in the admin section for themes.

The file theme.css should contain a line starting with ‹Theme name: ›, followed by the theme name. Otherwise, just change the name and slug in the admin section. Your theme will completely supersede the built-in theme.

### How do I associate a movie or a PDF?

Export the movie (MPEG-4 or WebM) or PDF to a screen shot, and then upload it is a regular image 2000+ px. Then upload the movie or PDF in the admin section, associating it with the image.

Usually the reverse process is taking place, uploading a movie or a PDF to the server, which processes the file. But then again, most servers do not have ffmpeg or ghostscript installed. And with this method, you have full control of the poster image.

Movies will play on the image page (medium), while PDFs will be shown as originals in an overlay.

### How do I export AVIF and WebP images?

GIMP has native export for both formats. For AVIF, the standard quality setting 50% is a good starting point, together with 12 bit format. More bits doesn’t mean larger files – on the contrary, AVIF will benefit from smoother gradients.

Photoshop has native export of WebP, but it is inferior to Google’s plugin WebPShop. Photoshop can also export AVIF, but it is a more complicated process. In Camera Raw’s tech preview preferences, check «HDR output», and in file handling, set «automatically open all supported TIFFs».

Then save your edited 16 bit image as a flat TIFF, and open in Photoshop. Now you can choose export as AVIF from the interface. Photoshop export is limited to 10 bit per channel. On the other hand, you can define HDR output for monitors with higher bit depth. But unless you have such a monitor, you are operating in the dark.

![shahsin](https://github.com/dougherty-dev/shashin/assets/71740645/a43361cd-ad63-40a0-940f-5ae3eb71fa07)

phpstan analyse -l9 -a /shashin/functions/constants.php /shashin: [OK]
