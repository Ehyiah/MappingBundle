# MappingBundle
Symfony bundle to automap an object into another object easily.

It was mainly built in order to avoid passing full entities to symfony forms when you want to edit them. But you can use it in many ways.

It was designed to be as easy as possible, 
all the mapping is located into a single object that is "aware" of the target class and properties destinations.
There is no configuration outside of the main object.

The main service have two methods :

- mapToTarget : This method will map the mapped Aware object to a target object. The target object can be another simple object or an entity.
e.g : You have a form with a data_class that is an object that will represent some properties of an entity, for a User for example.

- mapFromTarget : this method will do the opposite. It will take an object (or an entity that is tagged as the target) and map properties into the mapped Aware object.

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```sh
$ composer require ehyiah/mapping-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```sh
$ composer require ehyiah/mapping-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    <vendor>\<bundle-name>\<bundle-long-name>::class => ['all' => true],
];
```

# <u>Usage</u>
In order to use this bundle, you will need to mark an object (basically a DTO) with the attribute Ehyiah\MappingBundle\Attributes\MappingAware

And in the same class, simply tag every properties you want to map to the target object with the same attribute.
If the properties in the target class and the mapped DTO have the same name, there is nothing else to do (see ```$text``` property example). If the properties have
a different name, you can use the ```target``` option to specify the name in the target class.

As the mapping logic is using the propertyAccess component, you can specify nested properties in the target class. see ```zipcode``` and ```street``` properties in the example below.

## <u>Simple Usage</u>

```php
    // src/DTO/MyAwesomeDTO

    use App\src\Entity;
    use Ehyiah\MappingBundle\Attributes;
    
    #[MappingAware(target: MyAwesomeEntity::class)]
    class MyAwesomeDTO
    {
        #[MappingAware]
        public string $text

        #[MappingAware(target: 'aBooleanProperty')]
        public bool $active
        
        #[MappingAware(target: 'address.zipcode')]
        public bool $zipcode
        
        #[MappingAware(target: 'address.street')]
        public bool $street
    }
```

If you call ```mapToTarget()``` function of the service in a controller, handler or wherever you need it, the service will take properties tagged with MappingAware attribute
and will put the values stored in the DTO into the properties in the entity and flush them (default value).


# <u>Advanced usage</u>

## <u>Transformers</u>

Sometimes you need to modify data between the objects.

Example : in your SourceObject you have a string and need a Datetime in the TargetObject.
Or the opposite

Well there is a simple way to do this via Transformers.
You can easily create them or use some of prebuilt.
To create them just create a class and implements ```TransformerInterface```

Transformers will have 2 methods ```transform``` and ```reverseTransform```. and you can pass an array of options that will be used in both.

```transform``` method is used in mapToTarget

```reverseTransform``` method is used in mapFromTarget

In each method you have access ot the SourceObject and the TargetObject.

```php
    // src/DTO/MySourceObject

    use Ehyiah\MappingBundle\Attributes;
    
    #[MappingAware(target: MyTargetObject::class)]
    class MySourceObject
    {
        #[MappingAware(transformer: \Ehyiah\MappingBundle\Transformer\DateTimeTransformer::class, options: ['option1' => 'value1'])]
        public string $date
    }
```

```php
    // src/DTO/MyTargetObject

    class MyTargetObject
    {
        public DateTime $date
    }
```

## <u>Going Further with Transformers</u>
Transformers can be <u>"open-minded"</u> or <u>"narrow-minded"</u>.
For a better understanding there is an easy-to-understand example built-in with ```StringToDateTimeTransformer``` and ```DateTimeTransformer```.

Which one to choose is entirely <u>to your mind</u> !
The only difference between them is how you code the transform and reverseTransform methods.

### <u>Narrow-minded transformers</u>
If you pick as examples the ```StringToDateTimeTransformer``` : It is said as a <u>narrow-minded</u> transformer as it will only accept to transform String to DateTime and reverseTransform DateTime to String.

If you try to do the opposite an Exception will be thrown.

### <u>Open-minded transformers</u>
If you pick as examples the ```DateTimeTransformer``` : It is said as an <u>open-minded</u> transformer as it will accept :
- to transform String to DateTime and reverseTransform DateTime to String.
- but also to transform Datetime to String and reverseTransform String to Datetime.

## List Of built-in Transformers
### Open-minded :

|     Transformer     |                         transform                         |                    reverseTransform                    |                                                           options                                                            |
|:-------------------:|:---------------------------------------------------------:|:------------------------------------------------------:|:----------------------------------------------------------------------------------------------------------------------------:|
| DateTimeTransformer |         string to DateTime or DateTime to string          |        string to DateTime or DateTime to string        |         ```format``` (use to transform the string with the provided format)<br/> example  ```'format' => 'Y/m/d'```          |
| BooleanTransformer  |     string\|int to boolean or boolean to string\|int      |    string\|int to boolean or boolean to string\|int    | ```trueValue``` or ```falseValue``` example: ```'trueValue' => 'MyCustomTrueValue'```<br/><br/> ```strict``` : true or false |
| EnumTransformer     | Enum or array of an Enum into string or array of string   | string or array of strings to enum or array of enum    |                      ```enum``` the class of the enum example : ```'enum' => MyEnum::class```                                |
|                     |                                                           |                                                        |                                                                                                                              |

### Narrow-minded:
|         Transformer         |                  transform                  |                   reverseTransform                   |                                                                         options                                                                         |
|:---------------------------:|:-------------------------------------------:|:----------------------------------------------------:|:-------------------------------------------------------------------------------------------------------------------------------------------------------:|
| StringToDateTimeTransformer |             string to DateTime              |                  DateTime to string                  |                       ```format``` (use to transform the string with the provided format)<br/> example  ```'format' => 'Y/m/d'```                       |
| StringToBooleanTransformer  |              string to Boolean              |                  Boolean to string                   | ```trueValue``` or ```falseValue``` (used in reverseTransform only)    ```'trueValue' => 'MyCustomTrueValue'``` <br/><br/> ```strict``` : true or false |
|  StringToEnumTransformer    | Keep original value (enum or array of enum) | string or array of strings to enum or array of enum  |                                      ```enum``` the class of the enum example : ```'enum' => MyEnum::class```                                           |
|                             |                                             |                                                      |                                                                                                                                                         |
