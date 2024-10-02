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

If you want to ignore ``null`` values when mapping from an object to the other, juste use the ``ignoreNullValue`` option on each properties you need to.

## <u>Simple Usage</u>

1 - Create a DTO that will 'hold' the mapping logic
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

2 - Inject the ```MappingServiceInterface``` into your code, and call the methods.

```php
    use App\src\Entity\MyAwesomeEntity;
    use App\src\DTO\MyAwesomeDTO;
    use Ehyiah\MappingBundle\Attributes;
    use Ehyiah\MappingBundle\MappingServiceInterface;
    
    class SomeUsefulServiceOrHandlerOrController
    {
        public function __construct(
            private MappingServiceInterface $mappingService,
        ) {
        }

        public function handle()
        {
            $entity = new MyAwesomeEntity();
            $dto = new MyAwesomeDTO();
            
            $this->mappingService->mapToTarget($dto, $entity);
            $this->mappingService->mapFromTarget($entity, $dto);
        }
    }
```
If you call ```mapToTarget()``` function of the service tagged with ```MappingServiceInterface``` in a controller, handler or wherever you need it, the service will take properties tagged with MappingAware attribute
and will put the values stored in the DTO into the properties in the entity and flush them (default value).


# <u>Advanced usage</u>

## <u>Replace the Built-in service</u>
A Default service implementing ```MappingServiceInterface``` exist in the bundle. But you may need to create a service that implements the ```MappingServiceInterface``` to replace the default built-in.

### Create your own service
To create your own service :
- Just create a service that implements the ```MappingServiceInterface```, there is nothing more to do, no need to register or override the default built-in. The compiler pass do the work for you.
- In this service you will handle the logic yourself.
- Of course you can take the basis methods of the default service and modify them to your needs inside your custom service.

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
It will pick a string from the sourceObject and transform it into a DateTime object in the target object (via the mapToTarget method). And will pick the DateTime object from the target object and reverseTransform it into the sourceObject (via the mapFromTarget method)

If you try to do the opposite an Exception will be thrown.

### <u>Open-minded transformers</u>
If you pick as examples the ```DateTimeTransformer``` : It is said as an <u>open-minded</u> transformer as it will accept :
- to transform String to DateTime and reverseTransform DateTime to String.
- but also to transform Datetime to String and reverseTransform String to Datetime.

## List Of built-in Transformers
### Open-minded :

|     Transformer     |                     transform and reverseTransform                     |                                                                                                       options                                                                                                        |
|:-------------------:|:----------------------------------------------------------------------:|:--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| DateTimeTransformer |                string to DateTime OR DateTime to string                | ```format``` (use to transform the string with the provided format)<br/> example  ```'format' => 'Y/m/d'```  <br/> ```timezone``` a valid DateTimeZone object example : ```'timezone' => new DateTimeZone('UTC')```  |
| BooleanTransformer  |            string\|int to boolean OR boolean to string\|int            |                                             ```trueValue``` or ```falseValue``` example: ```'trueValue' => 'MyCustomTrueValue'```<br/><br/> ```strict``` : true or false                                             |
| EnumTransformer     | Enum\|Enum[] into string\|string[] OR string\|string[] to enum\|enum[] |                                                                       ```enum``` the class of the enum example : ```'enum' => MyEnum::class```                                                                       |
|                     |                                                                        |                                                                                                                                                                                                                      |

### Narrow-minded:
|         Transformer         |                      transform                      |                                                                               transform available options                                                                               |                  reverseTransform                   |                                                                                             reverseTransform available options                                                                                              |
|:---------------------------:|:---------------------------------------------------:|:---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|:---------------------------------------------------:|:---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| StringToDateTimeTransformer |                 string to DateTime                  |                                            ```timezone``` a valid DateTimeZone object example : ```'timezone' => new DateTimeZone('UTC')```                                             |                 DateTime to string                  |                                                         ```format``` (use to transform the string with the provided format)<br/> example  ```'format' => 'Y/m/d'```                                                         |
| StringToBooleanTransformer  |                  string to Boolean                  |                                                                              ```strict``` : true or false                                                                               |                  Boolean to string                  |                                                                   ```trueValue``` or ```falseValue```  example:  ```'trueValue' => 'MyCustomTrueValue'```                                                                   |
|   StringToEnumTransformer   | string or array of strings to enum or array of enum |                                                        ```enum``` the class of the enum example : ```'enum' => MyEnum::class```                                                         | enum or array of enum to string or array of strings | ```enum``` the class of the enum example : ```'enum' => MyEnum::class```  <br>   ```return``` in case you would need to return the NAME instead of the VALUE example: ```return => StringToEnumTransformer::RETURN_NAME ``` |
|    CollectionTransformer    |    collection of object to collection of object     | ```targetClass``` the expected class for elements in the collection (mandatory), ```fillFrom``` the collection property from where you would load data to simulate ```clearMissing = false``` |    collection of object to collection of object     |                                                                        ```sourceClass``` the current class of elements in the collection (mandatory)                                                                        |
|                             |                                                     |                                                                                                                                                                                         |                                                     |                                                                                                                                                                                                                             |

### Mapped collection inside Mapped object
If you need to have sub mapped elements inside your initial mapped class, the ```CollectionTransformer``` is the perfect fit.
- it can allow you to map collection elements to another (or also same to benefit of other ```CollectionTransformer``` feature!) class
- it can simulate the clearMissing feature to avoid default mapping clear on element not posted
- it can be deep chained
