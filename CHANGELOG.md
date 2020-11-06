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
