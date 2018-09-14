# Router

*Observe: This project is still a work in progress. Before version 1.0.0 it should be seen as highly unstable.*


Hrafn-Router is a http router based on a tree structure for requests. Each part of a request target pattern defined through the router 
is parsed by a `PathExtractor` and then built to a node-tree.  
Each action is attached to a node by reference and then simply fetched from a hash map structure when needed.

The Router supports Parameter injection by using a `ParameterExtractorInterface` implementation which plucks
parameters from a request target while matching it to the pattern defined in the router.

 The router currently utilizes the following PSR standards:
 
 * PSR4 Auto-loading
 * PSR3 Logging
 * PSR7 Messages
 * PSR11 Container
 * PSR15 Handlers

---

More information, documentation and development guidelines will be supplied before version 1.0.0 of the router.
