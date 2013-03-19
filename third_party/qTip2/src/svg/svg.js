PLUGINS.svg = function(api, svg, corner, adjustMethod)
{
	var doc = $(document),
		elem = svg[0],
		result = {
			width: 0, height: 0,
			position: { top: 1e10, left: 1e10 }
		},
		box, mtx, root, point, tPoint;

	// Ascend the parentNode chain until we find an element with getBBox()
	while(!elem.getBBox) { elem = elem.parentNode; }

	// Check for a valid bounding box method
	if (elem.getBBox && elem.parentNode) {
		box = elem.getBBox();
		mtx = elem.getScreenCTM();
		root = elem.farthestViewportElement || elem;

		// Return if no method is found
		if(!root.createSVGPoint) { return result; }

		// Create our point var
		point = root.createSVGPoint();

		// Adjust top and left
		point.x = box.x;
		point.y = box.y;
		tPoint = point.matrixTransform(mtx);
		result.position.left = tPoint.x;
		result.position.top = tPoint.y;

		// Adjust width and height
		point.x += box.width;
		point.y += box.height;
		tPoint = point.matrixTransform(mtx);
		result.width = tPoint.x - result.position.left;
		result.height = tPoint.y - result.position.top;

		// Adjust by scroll offset
		result.position.left += doc.scrollLeft();
		result.position.top += doc.scrollTop();
	}

	return result;
};

