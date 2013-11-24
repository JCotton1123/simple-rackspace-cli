simple-rackspace-cli
====================

## Description

A script for working with the Rackspace API at the command-line.

Goals: 

* Minimize depedencies - as you can see from the requirements it couldn't get much leaner unless it was written in bash (ugly). 
* Minimize abstraction - it only abstracts away the details of obtaining an auth token, looking up the appropriate URL for the specified service and region, and the details of making the HTTP requests.

## Requirements

* PHP >= 5.3
* PHP-curl

## Usage

This script relies on a configuration file that must contain the identity api endpoint and your username and api key. A sample config file is provided with this code. If you do not supply a config file, it will try to load *.simplerackspacecfg* from within your current working directory.

Calling the script is pretty straightforward...

```
usage: simple-rackspace-cli.php [-c config-file] [-s service] [-r region] [-u uri] [-m http-method] [-d data]
options:
 -c The path to a simple-rackspace-cli config file.
 -s The service you're contacting.
 -r The region you would like to execute this operation within.
 -u The *relative* URI for the endpoint you're trying to reach.
 -m The HTTP method.
 -d An optional block of data, usually JSON, supplied as an argument value or piped in, that will be sent with your request.
```

It will return the output, typically JSON, and set the exit code based on the http status recieved (200 = 0, 300 = 3, 400 = 4, 500 = 5).

### Examples

*Create a Next-Gen server*

```
$ ./simple-rackspace-cli.php -s cloudServersOpenStack -u /servers -m POST -d\
'{
    "server" : {
        "name" : "api-test-server-1",
        "imageRef" : "3afe97b2-26dc-49c5-a2cc-a2fc8d80c001",
        "flavorRef" : "2"
    }
}' > /dev/null
$ echo $?
0
$ ./simple-rackspace-cli.php -s cloudServersOpenStack -u /servers -m POST -d < create-server.json 
{"server": {"OS-DCF:diskConfig": "AUTO", "id": "35379c68-d723-4f1f-84ed-39ed93cc09c8", "links": [{"href": "https://dfw.servers.api.rackspacecloud.com/v2/11111/servers/35379c68-d723-4f1f-84ed-39ed93cc09c8", "rel": "self"}, {"href": "https://dfw.servers.api.rackspacecloud.com/11111/servers/35379c68-d723-4f1f-84ed-39ed93cc09c8", "rel": "bookmark"}], "adminPass": "Mumb3f5vpPrX"}}
$ echo $?
0
$ ./simple-rackspace-cli.php -s cloudServersOpenStack -u /servers -m POST -d < /dev/null
{"badRequest": {"message": "The server could not comply with the request since it is either malformed or otherwise incorrect.", "code": 400}}
$ echo $?
4
```

### Services

* cloudFiles
* cloudFilesCDN
* cloudDatabases
* cloudLoadBalancers
* cloudBlockStorage
* cloudServersOpenStack (Next gen servers)
* cloudDNS
* cloudMonitoring
* autoscale
* cloudBackup
* cloudServers (First gen servers)

### Regions

* ORD
* DFW
* IAD
* SYD
* HKG

## Known issues

* Some of the services do not require a region. This will require a small amount of refactoring to fix.

