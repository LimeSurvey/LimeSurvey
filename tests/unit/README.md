# Unit tests
from: 
https://stackoverflow.com/questions/4904096/whats-the-difference-between-unit-functional-acceptance-and-integration-test
   > Unit tests should be focussed on one particular feature (e.g., calling the pop method when the stack is empty should throw an InvalidOperationException). Everything it touches should be done in memory; this means that the test code and the code under test shouldn't:
   >- Call out into (non-trivial) collaborators
   >- Access the network
   >- Hit a database
   >- Use the file system
   >- Spin up a thread
   > 
   >Any kind of dependency that is slow / hard to understand / initialise / manipulate should be stubbed/mocked/whatevered using the appropriate techniques so you can focus on what the unit of code is doing, not what its dependencies do. 