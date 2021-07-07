## 20210706
### Added
- None
### Changed
- Fixed: Changed default ports to high ports. Docker image no longer uses ports that require root privilege to run - https://github.com/yesinteractive/kong-map/issues/20
- Fixed: Mixed content on view/edit cluster - https://github.com/yesinteractive/kong-map/issues/15
- Fixed: fsl_curl return 0 when Kong admin port is https - https://github.com/yesinteractive/kong-map/issues/19
- Updated Kong decK version to 1.7.0.
### Removed
- None


## 20210329
### Added
- None
### Changed
- Updated Kong decK version to 1.5.1.
### Removed
- None


## 20210120
### Added
- None
### Changed
- Fixed: Pop-ups don't show content correctly in map view: https://github.com/yesinteractive/kong-map/issues/10
### Removed
- None

## 20201218
### Added
- None
### Changed
- Fixed Declarative configs were not displaying in editor for Kong Enterprise 2.2 on the default workspace. This has been resolved.
### Removed
- None

## 20201109
### Added
- Counts for Kong entities (services, routes, plugins) on Cluster Map cluster entity.
- Route Analyzer now shows upstream targets configured for a service.
### Changed
- Fixed Export Config with ID button function. Both buttons were previously exporting without ID's only.
### Removed
- None


## 20201106
### Added
- Ability to edit specific routes, plugins, and services directly from cluster map view. Click on map node
to reveal the edit button. Clicking the edit button will open the config editor and highlight the element to be directly edited in the config.
- Ability to edit specific routes, plugins, and services in route analyzer. Clicking the edit button
will open the config editor and highlight the element to be directly edited in the config.
- Toggle to view declarative config with or with entity ID's
### Changed
- The `kong_ent_manager_url` JSON parameter when set to `null` will hide the Kong Manager buttons
form KongMap for that cluster, even though the cluster is an enterprise cluster.
- Export buttons to offer options to export with or without entity ID's.
### Removed
- Kong Logo from KongMap header.


## 20201101
### Added
- Docker compose install examples.
- Tags, which are now searchable, to all routes and services nodes in cluster map.
### Changed
- Filter in cluster view can search and filter to all details of an element of map such as tag name, or url or a service, etc. Previously could only filter on node name.
- Fixed word wrap issues in web app view template. 
- Added Kong, Inc. trademark disclaimers.
- Other misc page formatting.
### Removed
-None


## 20201026
### Added
- Confirmation dialog when about to save new declarative configuration.
- Enhancement Request - Make map searchable. Will enhance this feature further in future. For now acts as a filter rather than proper search and highlight.  https://github.com/yesinteractive/kong-map/issues/2 

### Changed
- Altered cluster details displayable data in cluster view
- Altered key value data appearance in cluster/map view

### Removed
- None


## 20201022
### Added
- None

### Changed
- Fixed - Custom plugin throws error when analyzing endpoint]) https://github.com/yesinteractive/kong-map/issues/1
- Updated Config Editor buttons and restyled some UI elements

### Removed
- None
