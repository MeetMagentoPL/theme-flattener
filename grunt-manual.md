# Grunt watcher

Install grunt according to magento 2 documentation (http://devdocs.magento.com/guides/v2.0/frontend-dev-guide/css-topics/css_debug.html#grunt_prereq)

To the file /dev/tools/grunt/configs/watch.js add new watcher:

var watchThemeFlatten = {
    'themeFlat': {
        'files': '<%= path.themeFlatten%>/<watching flat directory>/*.less',
        'tasks': 'themeChanged'
        
    }
};

as <watching flat directory> add directory name with simlinks to watch

and change:
module.exports = _.extend(themeOptions, watchOptions);

to:

module.exports = _.extend(themeOptions, watchOptions, watchThemeFlatten);

add themeflatten.js to /dev/tools/grunt/configs/ directory

in themeflatten.js
in variable: commandToBeExecuted = "php <command to be executed>";

add php command which shall be executed after any simlink change


