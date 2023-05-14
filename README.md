# SimPic

SimPic is a web-based application that allows users to store and organize their images in directories. It provides an easy-to-use interface for uploading, moving, and deleting directories and images.
![praesentation02](https://github.com/flbrgit/SimPic/assets/92446154/c6fdcf69-86d3-4ae0-b716-abd8759006d2)

## Installation

To install SimPic, follow these steps:

1. Clone the repository from Github.
2. Install XAMPP.
3. Install Python >3.8
4. Install python mysql library
5. Adjust the path of the python executable in "browser.ini"
6. Adjust the path of the python executable and the root directory of the installation in "/static/settings.json"
7. Open phpmyadmin and import the four *.sql-files inside the root of the installation directory
8. Start XAMPP control panel
9. Start the database and apache

## Usage

Once the server is running, you can access the SimPic application by navigating to `http://localhost/SimPic/` in your web browser.

### Creating Directories

To create a new directory, click on the "New Directory" button in the top right corner of the screen. Enter a name for the directory and click "Create". The new directory will appear in the list of directories on the left side of the screen.

### Uploading Images

To upload images to a directory, first select the directory by clicking on its name in the list on the left side of the screen. Then, click on the "Upload Images" button in the top right corner of the screen. Select one or more image files from your computer and click "Upload". The images will be added to the selected directory.

### Moving Images

To move an image from one directory to another, first select the directory containing the image by clicking on its name in the list on the left side of the screen. Then, click on the image to select it. Click on the "Move Image" button in the top right corner of the screen. Select the destination directory from the dropdown menu and click "Move".

### Deleting Directories and Images

To delete a directory or image, first select it by clicking on its name in the list on the left side of the screen. Then, click on the "Delete" button in the top right corner of the screen. Confirm that you want to delete the directory or image.

## Additional Features

### Roles and Permissions

SimPic has plans to implement further roles and permissions.

### Performance

The performance loading pages with directories is not affected by a larger amount of images inside, because those images will load dynamically. It only can last several seconds to upload multiple files inside the application, as of course they have to be transmitted over the internet and stored in the database.

## Screenshots

![praesentation01](https://github.com/flbrgit/SimPic/assets/92446154/63c4ce71-ca8a-427e-85f3-bdb760c7380b)
![praesentation03](https://github.com/flbrgit/SimPic/assets/92446154/9c5891d3-0481-43cd-9b3d-8bf697fb41bc)

## Copyright

Copyright © 2023 Florian Briksa

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation 
the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE 
AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN 
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

This program can be shared, copied and modified, but you have to name the author, Florian Briksa.
