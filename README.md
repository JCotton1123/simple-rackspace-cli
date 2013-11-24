simple-rackspace-cli
====================

## Description

This script makes working with the Rackspace API easier without abstracting away too much. It abstracts away the details of obtaining an auth token, looking up the appropriate URL for the specified service and region, and calling the API.

## Requirements

* PHP >= 5.3
* PHP-curl

## Usage

This script relies on a configuration file, .simplerackspacecfg, to be present in the directory this script is launched. This configuration file must contain the identity api endpoint and your username and api key. A sample .simplerackspacecfg file is available in this repo.

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

Detailed listing of Next-Gen servers within the DFW region

```
./simple-rackspace-cli.php -s cloudServersOpenStack -r DFW -u /servers/details
```

Create a Next-Gen server

```
./simple-rackspace-cli.php -s cloudServersOpenStack -u /servers -m POST -d\
'{
    "server" : {
        "name" : "api-test-server-1",
        "imageRef" : "3afe97b2-26dc-49c5-a2cc-a2fc8d80c001",
        "flavorRef" : "2"
    }
}'

OR 

./simple-rackspace-cli.php -s cloudServersOpenStack -u /servers -m POST -d < create-server.json

{"server": {"OS-DCF:diskConfig": "AUTO", "id": "35379c68-d723-4f1f-84ed-39ed93cc09c8", "links": [{"href": "https://dfw.servers.api.rackspacecloud.com/v2/11111/servers/35379c68-d723-4f1f-84ed-39ed93cc09c8", "rel": "self"}, {"href": "https://dfw.servers.api.rackspacecloud.com/11111/servers/35379c68-d723-4f1f-84ed-39ed93cc09c8", "rel": "bookmark"}], "adminPass": "Mumb3f5vpPrX"}}
```

## Why ???

* Learn how the Rackspace, and to some degree OpenStack, API works.
* Minimize dependencies (since it is meant as a CLI tool)
* Minimize abstraction


