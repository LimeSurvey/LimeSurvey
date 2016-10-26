# Taxonomy Browser TODOs
## Fancytree

  - bootstrap theme 'jumps' just before expand animation ends

  - ext-wide: broken selecton bar when depth > 20

  - smart expand mode
    The tree gets way too large

  - Search could be improved.
    - restrict search row height to 1 em
    - DOWN key in search box should focus first result entry
    - After clicking a link, we should jump to the details?
 	  - Result should be collapsibel without dismissing the query?

  - concept: 'sparse tree': `node.partload = true`.
    only shows the parent paths, but knows that those parents may have more children, 
    that need to be reloaded later.
    -> use to load Path to target species without reqursive loadPath requests.

  - Table:
    Support re-definition of colum templates (i.e. adding removing columns)
    - https://github.com/mar10/fancytree/issues/436


## GUI

  - Marine / Extinct / Terrestrial may have multiple values
  - Print mode
