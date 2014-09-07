#deli

*deli* is an abstract delivery system for request/response type applications. It can be used as a base for web and
CLI delivery systems such as [curir] and [commander].

The base idea is that a user request consists of a path and parameters which is then mapped to a method call. The return value
of this method call is then delivered as a response to the user.

[curir]: http://github.com/watoki/curir
[commander]: http://github.com/watoki/commander