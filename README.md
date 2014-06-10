php-enum
=======

This project is an Enumeration implementation for PHP which is based on Java Enumerations.

Normally, I don't try to make force one language's constructs into a different language. However, in my time with Java I have grown to appreciate their Enum data type. I believe the functionality offered is significant enough to warrant a php implementation.

In software development a list of available values is inevitably necessary. These are often represented as class constants such as this:

```php
class Colors
{
  const RED = 1;
  const BLUE = 2;
  ...
}

```

Often, this is sufficient but there are some limitations of this approach. This projects strives to overcome those limitations.

In the example of colors, it is obvious that red is red and blue is blue. That immutable nature of colors suggest that a constant is a good fit.  However, there is much more to a color than the identifier offered by a set of class constants. Each color has an immutable red, green, and blue value.  Trying to represent this complex, constant structure as normal constants is impractical if not infeasible.  This is the first use case for a php-enum implementation. An enum can be described as a "finite set of values which are each represented by a complex structure that is comprised SOLELY of constant values".  Colors fit this definition.

Continuing with the example of Colors, it is reasonable to say that there are known operations which can be carried out against the constant attributes of a color. For example, deriving the html code for a color. Whereas a giant mapping could be maintained which relates class constants (each identifying a specific color) to hex codes such an implementation would be difficult to maintain. This problem can be solved with an Enum implementation. Each Enum value is a class instance and, as such, can have methods.  

... more to come ...



I feel like it is important to point out that, while this implementation is of my own design, the feature set is not novel and is strongly inspired by the Java Enum data type. For further reading on the Java Enum data type, see: http://docs.oracle.com/javase/tutorial/java/javaOO/enum.html
