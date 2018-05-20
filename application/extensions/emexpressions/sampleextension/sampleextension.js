// extending LEM wit js functions similar to the php file with same name
// js functions are required for group-by-group or all-in-one presentations of surveys

function sayHello(msg)
{
	return 'Hello ' + msg;
}

function sayBye(msg)
{
	return 'Good Bye ' + msg + ". " + notRegistered();
}

function notRegistered()
{
	return "Result is 42!";
}
