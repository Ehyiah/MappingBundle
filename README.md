# MappingBundle
Symfony bundle to automap an object into another object easily.

It was mainly built in order to avoid passing full entities to symfony forms when you want to edit them. But you can use it in many ways.

It was designed to be as easy as possible, all the mapping is located into a single object that is "aware" of the target class and properties destinations.
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
If the properties in the target class and the mapped DTO have the same name, there is nothing else to do (see $text property example). If the properties have
a different name, you can use the target to specify the name in the target class.

As the mapping logic is using the propertAccess component, you can specify nested properties in the target class. see zipcode and street properties.

There is nothing else to do.

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

If you call mapToTarget() in a controller, handler or wherever you need it, the service will take properties tagged with MappingAware and will put the values stored in the DTO 
into the properies in the entity and flush them (default value).


## <u>Advanced usage</u>

### <u>Transformers</u>

Sometimes you need to modify data between the objects.
Example : in your SourceObject you have a string and need a Datetime in the TargetObject.
Or the opposite

Well there is a simple way to do this via the Transformers.
You can easily create them or use some of prebuilt.
To create them just create a class and implements TransformerInterface or ReverseTransformerInterface (or both if you need both)

Transformers can have 2 methods transform and reverseTransform. and you can pass an array of options that will be used in both.

In each methods you have access ot the SourceObject and the TargetObject.

<u>transform</u> method is used in mapToTarget

```php
    // src/DTO/MyAwesomeDTO

    use App\src\Entity;
    use Ehyiah\MappingBundle\Attributes;
    
    #[MappingAware(target: MyAwesomeEntity::class)]
    class MyAwesomeDTO
    {
        #[MappingAware(transform: \Ehyiah\MappingBundle\Transformer\StringToDateTimeTransformer::class, options: ['option1' => 'value1'])]
        public string $date
    }
```

<u>reverseTransform</u> method is used in mapFromTarget

```php
    // src/DTO/MyAwesomeDTO

    use App\src\Entity;
    use Ehyiah\MappingBundle\Attributes;
    
    #[MappingAware(target: MyAwesomeEntity::class)]
    class MyAwesomeDTO
    {
        #[MappingAware(reverseTransform: \Ehyiah\MappingBundle\Transformer\StringToDateTimeTransformer::class, options: ['option1' => 'value1'])]
        public string $date
    }
```

### <u>Going Further with Transformers</u>
Transformers can be <u>"open-minded"</u> or <u>"narrow-minded"</u>.
For a better understanding there is an easy to understand example built-in with ```StringToDateTimeTransformer``` and ```DateTimeTransformer```.

Which one to choose is entierely <u>to your mind</u> !

#### <u>Narrow-minded transformers</u>
If you pick as examples the ```StringToDateTimeTransformer``` : It is said as a <u>narrow-minded</u> transformer as it will only accept to transform String to DateTime and reverseTransform DateTime to String.

If you try to the the opposite an Exception will be thrown.

#### <u>Open-minded transformers</u>
If you pick as examples the ```DateTimeTransformer``` : It is said as an <u>open-minded</u> transformer as it will accept :
- to transform String to DateTime and reverseTransform DateTime to String.
- but also to transform Datetime to String and reverseTransform String to Datetime.
