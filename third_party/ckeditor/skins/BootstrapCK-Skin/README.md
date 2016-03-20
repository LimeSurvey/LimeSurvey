# About [BootstrapCK-Skin][BootstrapCK-Skin]

!Update: This skin is for CKeditor3 use. Want the new CKeditor4 skin? https://github.com/Kunstmaan/BootstrapCK4-Skin

The [BootstrapCK-Skin][BootstrapCK-Skin] is a skin for [CKEditor][CKEditor] based on [Twitter Bootstrap][Twitter Bootstrap] styles.<br />
Some things couldn't be changed inside the skin, like the smileys and the position of the browse buttons when inserting an image or a flash.<br />
Here's how you fix that:

## Smileys

Go to <code>plugins > smiley > dialogs</code> , and replace <code>smiley.js</code> with [this one][this one].<br />
And grab your new images over [here][here].

## Browse buttons

Go to <code>plugins > image > dialogs > image.js</code> and to <code>plugins > flash > dialogs > flash.js</code><br />
In both, change the margin-top to 17px (instead of 10px).

# Demo
[http://kunstmaan.github.com/BootstrapCK-Skin/][http://kunstmaan.github.com/BootstrapCK-Skin/]

[BootstrapCK-Skin]: https://github.com/Kunstmaan/BootstrapCK-Skin "BootstrapCK-Skin"
[CKEditor]: http://ckeditor.com/ "CKEditor"
[Twitter Bootstrap]: http://twitter.github.com/bootstrap/ "Twitter Bootstrap"
[this one]: http://kunstmaan.github.com/BootstrapCK-Skin/smiley.js "smiley.js"
[here]: http://kunstmaan.github.com/BootstrapCK-Skin/smileys.zip "smileys.zip"
[http://kunstmaan.github.com/BootstrapCK-Skin/]: http://kunstmaan.github.com/BootstrapCK-Skin/ "Demo"
[kunstmaan]: http://www.kunstmaan.be "Kunstmaan"
