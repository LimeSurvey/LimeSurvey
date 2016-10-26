## Vision
Fancytree should behave like a standard form control.

## Status
This feature is already implemented ([see demo](http://wwwendt.de/tech/fancytree/demo/sample-form.html)).

Yet there are still open questions, so this feature is open for discussion and the API is subject to change.

**Please discuss here: <https://github.com/mar10/fancytree/issues/999>**

## Requirements
Fancytree should behave 'well' when embedded into a form, i.e. comparable to 
a standard listbox:
  
- The tree container can be entered using TAB key.  
  Container gets `:focus` and `.fancytree-focus`  
  After that, UP/DOWN keys can be used to navigate inside the tree.  
- There is a visual feedback, when the tree has focus:  
  - the container get's a focus frame  
  - the activated node is stronlgy colored (but dimmed, when tree is not focused)
- Clicking a node also sets tree focus  
- But clicking inside an input control (or any other tabbable element) that 
  is part of a node, sets `.fancytree-focus` to the container and the node, 
  but `:focus` stays on the input
- The API should allow to set `:focus` to an embedded `<input>` when a node 
  receives a `focus` event.  
- When `:focus` is inside an embedded `<input>`, UP/DOWN should be disabled,  
  OR  
  UP/DOWN should remove `:focus` from the input
 
## Discussion
### Resources
- Listbox behavior in current browsers:
    - IE 10 / Win7:  
       
      Listbox is initially empty  
      Entering with TAB sets focus outline to first entry and sets listbox border color from gray to black.  
      Leaving with TAB removes focus frame from first entry and sets listbox border color to gray.
      When listbox has focus, SPACE will activate the entry (blue bg + focus outline).
      UP/DOWN will move focus and also activate.
      It is not possible to de-activate again!
       
      Focused listbox has a black border (gray otherwise)  
      Selected entry has a blue background (even if listbox is not focused).  
      If listbox was entered by TAB, there is also a dotted outline which is 
      also maintained when using UP/DOWN.
      If listbox was entered by mouse click, no dotted outline appears, even 
      when using UP/DOWN afterwards.
       
      Hovering sets listbox border to blue and entry bg to gray.
              
    - Firefox / Win7:  
      Entering with TAB sets dotted outline on first entry (use SPACE/UP/DOWN to select).  
      No hover effects.  
      Focused listbox has a gray border (same as unfocused)  
      Selected entry always has a blue background (with dotted outline when 
      listbox is focused)
      There is always a dotted outline when listbox has focus (no matter if entered with click or TAB).

    - Chrome / Win7:  
      Entering with TAB leaves the listbox empty (must use SPACE/UP/DOWN).  
      No hover effects.  
      Focused listbox has an orange border (gray otherwise)  
      Selected entry has a blue background (gray if listbox is not focused)
      There is never a dotted outline.
        
    - Safari / Win7:  
      Focused listbox has a blue glowing border (gray otherwise)
      Behaves as Chrome otherwise. 


### Known Problems

### Proposal


## Current Specification

```js
var s = "a", i = 42;
```