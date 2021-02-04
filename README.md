# PROJECT ARCHIVED

Code is left for historical reasons. If you want something good and modern use [Symfony Config](https://symfony.com/doc/current/components/config.html).

---

# Waffer
A tiny, fast and (if you want) powerful configuration library. It was wrote in intetion of usage with long running PHP daemons. It supports JSON & serializations configuration files out-of-box.

## FAQ

### Why *Waffer*?
When I started working on this code I need some sort of a name. Since it was intended to have smaller version along with bigger one (extending it) I came up with "diet" term stolen right from Panic Inc. and it's DietCoda app ;)  
Still eating waffers with maple syrup I laughed and created DietWaffer class. This name sticks.

### There's no XML!
Yup, you're right. I fully agree with Linus who's said: 
> XML is the worst format ever designed

I never had enough patience to deal with DOM trees, even with SimpleXML library onboard.  
If you wish XML to be supported I kindly welcome pull-request for that feature.

### Wait, where's INI?
INI format have a lot of limitations, and it's full of hacks. To be honest Waffer had INI support while ago, bud it was removed due to naming limitations.  
For further informations see ***Notes*** section in PHP documentation for [parse\_ini\_string()](http://php.net/parse-ini-string).


## Usage
Every library using Waffer is intended to have it it's default configuration. It's much like jQuery plugins - you can provide some extra options which overwrite default configuration.
Single instance of Waffer is intended to be [injected](http://en.wikipedia.org/wiki/Dependency_injection) into multiple objects, deep into application.

### Initialization
You can choose one of two classes - Waffer and DietWaffer. Second one contains only core configuration black-box, without any export/import capabilities.  
Initial configuration array can be specified as constructor parameter.

### (Re)storing data
Waffer was built to be simple, powerful and flexible tool. It supports many different ways of getting and storing configuration data. Let code below exaplains all of them:
``` php
$myAwesomeConfiguration = array(
    'version' => 1.1, //This is global configuration option
    'yummyWaffers' => 10,
    'ACME\FooBar' => array( //Configuration for "FooBar" library by ACME
        'version' => M_PI,
        'bakingTemp' => 280,
        'defaultOwner' => 'Mr. Foo'
    )
);

$config = new DietWaffer($myAwesomeConfiguration); //We don't need full Waffer for options below

//Basic usage
echo "Global version: ".$config->storage['version']."\n"; //Fastest but completly non-OO

$config->setVersion(1.2); //Magic setter
echo "New global version: ".$config->getVersion()."\n"; //...with magic getter

$config->yummyWaffers++; //You can also use magic property set
echo "No. of yummy waffers: ".$config->yummyWaffers."\n"; //...and get

//Namespaces
echo "ACME FooBar version: ".$config->storage['ACME\FooBar']['version']."\n";

$config->setVersion(3.1, 'ACME\FooBar'); //Magic setter
echo "New ACME FooBar version: ".$config->getVersion('ACME\FooBar')."\n"; //...with magic getter


//Removing variables
unset($config->yummyWaffers);

//Checking if variable exists
var_dump(
    isset($config->version),
    isset($config->yummyWaffers),
    isset($config->notin)
);

//Let's drive OO teachers crazy ;]
var_dump(
    $config(),
    $config('ACME\FooBar')
);
```

### Importing & exporting
Library supports JSON & (un)serialize oob. It also handles loading & saving settings from/to files.  
Let's a piece of code explain everything.
``` php
$config = new Waffer($myAwesomeConfiguration);

echo "JSON: ".$config->toJSON()."\n";
echo "Serialized: ".$config->serialize()."\n";
echo "Save to file ".print_r($config->toFile("example.conf"), true)."\n"; //This will save JSON file, you can pass Waffer::FORMAT_SERIAL to use serialization
//You can use fromFile() the same way
echo "PHP array: \n".$config."\n";
```

### Libraries integration
Waffer intrnally stores configuration as large array. By design every library using Waffer should register it's settings under own namespace key as shown below.
``` php
namespace ACME\FooBar;

class FooBar {
    public static $defaultConfigutation = array(
        "welecome" => "Hello %s!"
        "funFacts" => array(
            "There's disease named Maple syrup urine disease",
            "Defibrillated patient isn't going to jump out of the bed (unless you're in Hollywood)"
        )
    );
    
    private $config;
    
    public function __construct(DietWaffer $config) {
        $this->config = $config;
        $this->config->storage[__NAMESPACE__] = array_replace_recursive(self::$defaultConfigutation, (array)@$this->config->storage[__NAMESPACE__]); 
    }
}

```
