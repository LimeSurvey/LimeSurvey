## Image

### Accessible image descriptions

* People with visual impairments or visual processing disorders need
  accessible text descriptions of informative images.

* There are two types of accessible descriptions for images: *alternative
  text* and *long descriptions*.

### Alternative text

* Succinctly describes the content and function of the image for people
  with visual impairments, in no more than 100 characters

* Is required for informative images (but not for images that are purely
  decorative)

* Is not visible in the document; it is communicated to screen reader users
  via the *img* element's *alt* attribute

* For a purely decorative image, select the **‘Image does not require
  alternative text’** checkbox and leave the alternative text field blank.

### Long descriptions for complex images

* A complex image has informational content that cannot be described adequately
  by alternative text of 100 characters or less.

* Examples of complex images include charts, graphs, diagrams, scientific
  photographs and works of art.

* A complex image needs both a concise alternative text description and a
  longer, more detailed description.

* Accessibility requirements mandate that the long description of a complex
  image be placed in the document adjacent to the image.

### Adding a long description in the document

Follow these steps when adding a long description of a complex image:

1. Add an adequately detailed description of the image within the document,
   either just before or after the image (or both).

1. In answer to the question **‘Is a long description needed, and if so,
   where will it be located’**, select one of the following:
       * Yes; in the document before the image
       * Yes; in the document after the image
       * Yes; in the document before and after the image

By specifying the location of the long description relative to the image,
screen reader users will be informed of its existence and location in the
document. (This information is appended to the alternative text.)

### Insert a caption text box below the image

* A **caption** is an optional visual label supported by CKEditor for an image.
  It provides an additional way to describe an image that is immediately below
  and proximate to the image.

* The caption content is specified and is editable in the text box just below
  the image, once it has been inserted in the document.

* From an accessibility perspective the *caption* and the *alternative text*
  should not be the same, but instead should complement each other.

* Using the caption creates a *figcaption* element contained in a *figure*
  element. The *figure* element also contains the *img* element.

### Writing effective alternative text

The following are based on <a href="https://webaim.org" target="_resource">
WebAIM</a>'s guidelines for alternative text:

* **Be accurate and equivalent** in presenting the same *content* and
  *function* of the image.

* When **images are used as links**, the alternative text should describe the
  target of the link.

* **Be succinct.** This means the correct content and function of the image
  should be presented as succinctly as is appropriate. Typically no more than
  a few words are necessary, though rarely a short sentence or two may be
  appropriate. The length should not exceed 100 characters.

* **Do NOT be redundant** or provide the same information as text that is
  already part of the document.

* **Do NOT use the phrases “image of ...” or “graphic of ...”** to describe
  the image. Assistive technologies notify the user of the image.  It is your
  job to describe the purpose or the content of the image.  If the image is a
  photograph or illustration, etc. of important content, it may be useful to
  include this in the alternative text.

* **Do NOT include file names or sizes** as part of the alternative text.

### Why image descriptions are important

Adding text descriptions of images is an important part of making documents
accessible to the visually impaired who use assistive devices such as screen
readers and magnifiers, and to people with visual processing disorders, which
make especially complex images more difficult to understand.

When the user cannot see all or part of an image, assistive technologies will
read or display its alternative text description. This is especially important
when the image conveys information that is required for the user to fully
understand the information in the document.

For people with visual processing disorders, a detailed description of a
complex image helps them to more quickly or completely understand the
information being conveyed by the image.

### More information

* <a href="https://accessibility.psu.edu/images/alttext/"
target="_resource">Penn State: Image ALT Text</a>

* <a href="https://webaim.org/techniques/alttext/"
target="_resource">WebAIM: Alternative Text</a>

* <a href="https://www.w3.org/WAI/tutorials/images/"
target="_resource">W3C Web Accessibility Image Tutorial</a>

* <a href="http://diagramcenter.org/" target="_resource">Diagram Center</a>
