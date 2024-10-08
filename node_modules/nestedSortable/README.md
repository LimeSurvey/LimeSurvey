# nestedSortable jQuery plugin

# If you would like to help update/maintain or take over this project, please open an issue to let me know. I don't use this project anymore.

**nestedSortable** is a jQuery plugin that extends jQuery Sortable UI functionalities to nested lists.  

## Meteor Installation
    meteor add ilikenwf:nested-sortable

## What's new in version 2.0

The biggest change is that your nested list can now behave as a tree with expand/collapse funcionality. Simply set `isTree` to **true** in the options and you are good to go! Check the [demo](http://ilikenwf.github.io/example.html) out to see what can be done with nestedSortable and a little CSS. (Note that all **nestedSortable** does is to assign/remove classes on the fly)  
Also:
- **isAllowed** function finally works as expected, see the docs below
- Fixed: a small bug in the **protectRoot** function
- Changed: no drop zone will appear at all if you try to nest an item under another one that has the *no-nesting* class.
- Added: **doNotClear** option to prevent the plugin from deleting empty lists


## Features

- Designed to work seamlessly with the [nested](http://articles.sitepoint.com/article/hierarchical-data-database "A Sitepoint tutorial on PHP, MYSQL and nested sets") [set](http://en.wikipedia.org/wiki/Nested_set_model "Wikipedia article on nested sets") model (have a look at the `toArray` method)
- Items can be sorted in their own list, moved across the tree, or nested under other items.
- Sublists are created and deleted on the fly
- All jQuery Sortable options, events and methods are available
- It is possible to define elements that will not accept a new nested item/list and a maximum depth for nested items
- The root level can be protected
- The parentship of items can be locked, just as if it was a family tree. 

## Usage

```html
<ol class="sortable">
	<li><div>Some content</div></li>
	<li>
		<div>Some content</div>
		<ol>
			<li><div>Some sub-item content</div></li>
			<li><div>Some sub-item content</div></li>
		</ol>
	</li>
	<li><div>Some content</div></li>
</ol>
```

```js
$(document).ready(function() {

	$('.sortable').nestedSortable({
		handle: 'div',
		items: 'li',
		toleranceElement: '> div'
	});

});
```

Please note: every `<li>` must have either one or two direct children, the first one being a container element (such as `<div>` in the above example), and the (optional) second one being the nested list. The container element has to be set as the 'toleranceElement' in the options, and this, or one of its children, as the 'handle'.

Also, the default list type is `<ol>`.

*This is the bare minimum to have a working nestedSortable. Check the [demo](http://ilikenwf.github.io/example.html) out to see what can be accomplished with a little more.*

## Custom Options

<dl>
	<dt>disableParentChange (2.0)</dt>
	<dd>Set this to true to lock the parentship of items. They can only be re-ordered within theire current parent container.</dd>
	<dt>doNotClear (2.0)</dt>
	<dd>Set this to true if you don't want empty lists to be removed. Default: <b>false</b></dd>
	<dt>expandOnHover (2.0)</dt>
	<dd>How long (in ms) to wait before expanding a collapsed node (useful only if <code>isTree: true</code>). Default: <b>700</b></dd>
	<dt>isAllowed (function)</dt>
	<dd>You can specify a custom function to verify if a drop location is allowed. Default: <b>function (placeholder, placeholderParent, currentItem) { return true; }</b></dd>
	<dt>isTree (2.0)</dt>
	<dd>Set this to true if you want to use the new tree functionality. Default: <b>false</b></dd>
	<dt>listType</dt>
	<dd>The list type used (ordered or unordered). Default: <b>ol</b></dd>
	<dt>maxLevels</dt>
	<dd>The maximum depth of nested items the list can accept. If set to '0' the levels are unlimited. Default: <b>0</b></dd>
	<dt>protectRoot</dt>
	<dd>Whether to protect the root level (i.e. root items can be sorted but not nested, sub-items cannot become root items). Default: <b>false</b></dd>
	<dt>rootID</dt>
	<dd>The id given to the root element (set this to whatever suits your data structure). Default: <b>null</b></dd>
	<dt>rtl</dt>
	<dd>Set this to true if you have a right-to-left page. Default: <b>false</b></dd>
	<dt>startCollapsed (2.0)</dt>
	<dd>Set this to true if you want the plugin to collapse the tree on page load. Default: <b>false</b></dd>
	<dt>tabSize</dt>
	<dd>How far right or left (in pixels) the item has to travel in order to be nested or to be sent outside its current list. Default: <b>20</b></dd>
	<dt>excludeRoot</dt>
	<dd>Exlude the root item from the <code>toArray</code> output</dd>
</dl>

## Custom Classes (you will set them in the options as well)

<dl>
	<dt>branchClass (2.0)</dt>
	<dd>Given to all items that have children. Default: <b>mjs-nestedSortable-branch</b></dd>
	<dt>collapsedClass (2.0)</dt>
	<dd>Given to branches that are collapsed. It will be switched to <b>expandedClass</b> when hovering for more then <b>expandOnHover</b> ms. Default: <b>mjs-nestedSortable-collapsed</b></dd> 
	<dt>disableNestingClass</dt>
	<dd>Given to items that will not accept children. Default: <b>mjs-nestedSortable-no-nesting</b></dd>
	<dt>errorClass</dt>
	<dd>Given to the placeholder in case of error. Default: <b>mjs-nestedSortable-error</b></dd>
	<dt>expandedClass (2.0)</dt>
	<dd>Given to branches that are expanded. Default: <b>mjs-nestedSortable-expanded</b></dd>
	<dt>hoveringClass (2.0)</dt>
	<dd>Given to collapsed branches when dragging an item over them. Default: <b>mjs-nestedSortable-hovering</b></dd>
	<dt>leafClass (2.0)<dt>
	<dd>Given to items that do not have children. Default: <b>mjs-nestedSortable-leaf</b></dd>
	<dt>disabledClass (2.0)<dt>
	<dd>Given to items that should be skipped when sorting over them. For example, non-visible items that are still part of the list. Default: <b>mjs-nestedSortable-disabled</b></dd>
</dl>

## Custom Methods

<dl>
	<dt>serialize</dt>
	<dd>Serializes the nested list into a string like <b>setName[item1Id]=parentId&setName[item2Id]=parentId</b>, reading from each item's id formatted as 'setName_itemId' (where itemId is a number).
	It accepts the same options as the original Sortable method (<b>key</b>, <b>attribute</b> and <b>expression</b>).</dd>
	<dt>toArray</dt>
	<dd>Builds an array where each element is in the form:
<pre>setName[n] =>
{
	'item_id': itemId,
	'parent_id': parentId,
	'depth': depth,
	'left': left,
	'right': right,
}
</pre>
	It accepts the same options as the original Sortable method (<b>attribute</b> and <b>expression</b>) plus the custom <b>startDepthCount</b>, that sets the starting depth number (default is <b>0</b>).</dd>
	<dt>toHierarchy</dt>
	<dd>Builds a hierarchical object in the form:
<pre>'0' ...
	'id' => itemId
'1' ...
	'id' => itemId
	'children' ...
		'0' ...
			'id' => itemId
		'1' ...
			'id' => itemId
'2' ...
	'id' => itemId
</pre>
	Similarly to <code>toArray</code>, it accepts <b>attribute</b> and <b>expression</b> options.
	Optionally adding `data-` attributes will cause them to show up in the hierarchy. See demo for example.
	</dd>
</dl>

## Events
<dl>
       <dt>change</dt>
        <dd>Fires when the item is dragged to a new location.  This triggers for each location it is dragged into not just the ending location.
        <dt>sort</dt>
        <dd>Fires when the item is dragged.</dd>
        <dt>revert</dt>
        <dd>Fires once the object has moved if the new location is invalid.</dd>
        <dt>relocate</dt>
        <dd>Only fires once when the item is done bing moved at its final location.</dd>
</dl>

## Known Bugs

*nestedSortable* doesn't work properly with connected draggables, because of the way Draggable simulates Sortable `mouseStart` and `mouseStop` events. This bug might or might not be fixed some time in the future (it's not specific to this plugin).

## Requirements

jQuery UI Sortable 1.10+ (might work with 1.9, but not tested)

## Browser Compatibility

Tested with: Firefox, Chrome  
**NOTE: This is still an alpha version, please test thoroughly in whichever version of IE you target**

## License

This work is licensed under the MIT License.  
Which means you can do pretty much whatever you want with it.
