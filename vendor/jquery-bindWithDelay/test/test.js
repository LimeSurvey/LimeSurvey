module('onDelay');

asyncTest("delay", function() {
	var count		= 0;

	expect(2);
	
	jQuery(document).onDelay("foo", function() { ++count; }, 50);

	equal(count, 0, "Not incremented yet");

	jQuery(document).trigger("foo").trigger("foo").trigger("foo");

	setTimeout(function() {
		equal(count, 1, "Three triggers, one count");
		start();
	}, 100);
});