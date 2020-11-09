# KongMap #
Kongmap is a free visualization tool which allows you to view and declaratively edit configurations of
your Kong API Gateway Clusters, including Routes, Services, and Plugins/Policies. The tool is 
available for installation on Docker and Kubernetes only at this time.  

![GitHub](https://img.shields.io/github/license/yesinteractive/kong-map?style=for-the-badge)
[![Docker Pulls](https://img.shields.io/docker/pulls/yesinteractive/kongmap?style=for-the-badge)](https://hub.docker.com/r/yesinteractive/dadjokes) 
[![Version](https://img.shields.io/badge/version-20201109-green?style=for-the-badge)](https://hub.docker.com/r/yesinteractive/dadjokes) 

- [Features](#Features)
    - [Cluster View](#Cluster-View)
    - [Endpoint Analyzer](#Endpoint-Analyzer)
    - [Declarative Configuration Viewer/Editor](#Declarative-Configuration-Viewer-Editor)        
- [Compatibility](#Compatibility)
- [Docker Installation](#Docker-Installation)
- [Questions and Feedback](#Feedback-and-Issues)

## Features

#### Cluster View
Allows an admin to view a dynamic map of their Kong API Gateway clusters and visually see relationships between
Workspaces (for Kong Enterprise), Services, Routes (Endpoints), and Plugins (Policies). Cluster view can also
be used to see configuration of the proxy plane of your Kong for Kubernetes Ingress Controller. Clicking on any 
entity displays details of the entity and related links. Plugins can be toggled from view and map is searchable
(search by entity name, Kong tag, workspace, url, or any other details related to a Kong entity.) 

If editing is enabled, any Kong entity can be edited from the Cluster View map. Clicking on the edit button from
 any entity will send user directly to that entity in the declarative editor.


![alt text](https://github.com/yesinteractive/kong-map/blob/main/screenshots/kongmap-home.png?raw=true "kongmap")

#### Endpoint Analyzer
View details of an API Endpoint (Route). The analyzer shows the Service attached to the endpoint/route as well as provides
a breakdown of all plugins/policies in order of execution attached to the route/endpoint. For Kong Enterprise users,
all entities can be viewed directly via a link to Kong Manager.

If editing is enabled, any Kong entity can be edited from the Endpoint Analyzer map. Clicking on the edit button from
 any entity will send user directly to that entity in the declarative editor.

![alt text](https://github.com/yesinteractive/kong-map/blob/main/screenshots/kongmap-endpoint.png?raw=true "kongmap")


#### Declarative Configuration Viewer Editor
KongMap is deployed with a browser based implementation of Kong's CLI tool, decK. Here you can view, edit, and export
Kong declarative configurations for your open source and Enterprise clusters via YAML. Configurations can easily 
be copied and pasted from one Kong cluster to another or between workspaces. Declarative
configuration editing can be disabled by KongMap configuration, or managed via RBAC permissions if using Kong Enterprise. 

The Viewer/Editor can be invoked from the Cluster Map view by clicking on on any Kong entity, and from 
any element from the Endpoint Analyzer. Kong entity ID's can be toggled in and out of view with the viewer/editor.

![alt text](https://github.com/yesinteractive/kong-map/blob/main/screenshots/kongmap-deck.png?raw=true "kongmap")

## Compatibility
KongMap supports both Kong Open Source and Kong Enterprise Clusters greater than version 1.3 and supports both DB and Non-DB (dbless) Kong configurations.
KongMap also supports Kong for Kubernetes Ingress Controller versions greater than 0.5 (In Kong for Kubernetes,
the Ingress Controller's proxy container must have its Admin API exposed in some fashion.)

KongMap uses various public CDN's for various UI elements such as Bootstrap, jQuery, etc. so KongMap will not display
correctly in a browser on a closed network without Internet access.

## Docker Installation

Docker image is Alpine 3.11 based running PHP 7.3 on Apache. The container exposes both ports 80 an 443 with a self signed certificated. 

Below are instructions using the `docker run` command. For an example using `docker-compose`, see the example in the [examples directory folder.](https://github.com/yesinteractive/kong-map/blob/main/examples)

#### 1. Export Cluster Configurations to `KONG_CLUSTERS` Environment Variable

The connections to your Kong clusters are defined via JSON. The below example illustrates adding two Kong clusters to KongMap:

```json
{
  "my enterprise cluster": {
    "kong_admin_api_url": "http://kongapi_url:8001",
    "kong_edit_config": "true",
    "kong_ent": "true",
    "kong_ent_token": "admin",
    "kong_ent_token_name": "kong-admin-token",
    "kong_ent_manager_url": "http://kongmanager_url:8002"
  },
  "my kong open source cluster": {
    "kong_admin_api_url": "http://kongapi_url:8001",
    "kong_edit_config": "true",
    "kong_ent": "false",
    "kong_ent_token": "null",
    "kong_ent_token_name": "null",
    "kong_ent_manager_url": "null"
  }
}
  ```
Below is a definition of all variables in the KONG_CLUSTERS json config. All variables are required.

| Parameter              | Description | Required  |
|------------------------|-------------|-----------|
| `kong_admin_api_url`   | Full URL to Kong Admin API URL. Example: `http://kongadminapi:8001`     | `yes`     |
| `kong_edit_config`     | Boolean. Set to `true` to allow editing of Kong configs via KongMap. `false` will only allow readonly access to configs.           | `yes`     |
| `kong_ent`             | Boolean. Set `true` if you are connecting to a Kong Enterprise Cluster and to enable workspace support in KongMap. Only the default workspace will show if set to `false` and connected to a Kong Entperprise cluster. Otherwise set to `false`          | `yes`     |
| `kong_ent_token`       | The admin token for connecting to your Kong Enterprise Cluster Admin API. Set by RBAC configuration in Kong. Can be set to `null` if not needed.           | `yes`     |
| `kong_ent_token_name`  | The admin token header name for connecting to your Kong Enterprise Cluster Admin API.  Typically is `kong-admin-token`. Can be set to `null` if not needed.          | `yes`     |
| `kong_ent_manager_url` | Full URL to a Kong Manager if you wish to open entities in Kong Manager from KongMap. Can be set to `null` if not needed or if you do not want any Kong Manager buttons shown for the cluster.           | `yes`     |

Export the config to a variable:

```shell
 export KONG_CLUSTERS='{  "my enterprise cluster": {    "kong_admin_api_url": "http://kongapi_url:8001",    "kong_edit_config": "true",   "kong_ent": "true",    "kong_ent_token": "admin",    "kong_ent_token_name": "kong-admin-token",    "kong_ent_manager_url": "http://kongmanager_url:8002"  }}'
  ```

#### 2. Start Container

Run the container with the following command. Set the ports to your preferred exposed ports. The example below exposes KongMap on ports 8100 and 8143. 
Notice the `KONGMAP_URL` variable. Set this variable to the KongMap URL that you will connect to KongMap in your browser. For example, if 
running locally and exposing KongMap on port 8100, set to `http://localhost:8100`.

```
$ docker run -d \
  -e "KONGMAP_CLUSTERS_JSON=$KONG_CLUSTERS" \
  -e "KONGMAP_URL=http://url_to_kongmap:8100" \
  -p 8100:80 \
  -p 8143:443 \
  yesinteractive/kongmap
```


Full documentation available online here: [https://github.com/yesinteractive/kong-map/](https://github.com/yesinteractive/kong-map/)

#### 3. Authentication

If you want to enable authentication to KongMap's UI, it is recommended to run Kongmap behind your Kong Gateway and implement any authentication
policies you feel is appropriate (OIDC, OAUTH2, Basic Auth, etc.) at the gateway.

## Feedback and Issues

If you have questions, feedback or want to submit issues, please do so here: [https://github.com/yesinteractive/kong-map/issues](https://github.com/yesinteractive/kong-map/issues).