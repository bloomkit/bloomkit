Entity Framework
==================

The goal of the Entity framework is to provide a simple abstraction layer for defining
dataset-structures. 

Entity
---------
An Entity is the representation of a specific dataset - for example a customer. 
It contains specific values based on a dataset-structure which is defined by a
EntityDescriptor. An Entity also may contain dataset information like creation-date
modification-date, dataset-id etc.

EntityDescriptor
---------
The EntityDescriptor defines how a dataset should look like. Primarily it is a list of 
Fields with some additional informations to persist the data

Field
---------
The Field is the most granular element in the Entity framework. It represents a specific
element in the dataset-structure, like the birthday in the customer example from above.
Every Field has a specific type, it may be marked as searchable, hidden, mandatory etc.