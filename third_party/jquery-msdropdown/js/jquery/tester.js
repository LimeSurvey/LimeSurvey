/************** test case  **********************/
(function() {
var resultsTest;
this.assert = function assert(value, desc) {
	var li = document.createElement("li");
	li.className = value ? "pass" : "fail";
	var span = document.createElement("span");
	span.innerHTML = desc;
	li.appendChild(span);
	resultsTest.appendChild(li);
	if (!value) {
		li.parentNode.parentNode.className = "fail";
	}
	return li;
};
this.test = function test(name, fn) {
	resultsTest = document.getElementById("testResults");
	resultsTest = assert(true, name).appendChild(
	document.createElement("ul"));
	fn();
};
})();
function clearTestResult() {
	$("#testResults ul li").remove();
}
/***************************************/
