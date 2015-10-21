/**
 */
module.exports = function (grunt) {
    'use strict';
    var path   = require('./path');
    
  /* run php function after symlinks changed */
  grunt.task.registerTask('runThemeScript', function(){
        var scriptRun = require("child_process").exec,
            commandToBeExecuted = "php <command to be executed>";

    scriptRun(commandToBeExecuted, function(error, stdout, stderr) {
        if (!error) {
             console.log('php?');
        } else {
            console.log('no php');
        }
    });
});
  
  grunt.task.registerTask('themeChanged', function(){
    console.log('watcher works');
    
    grunt.task.run('runThemeScript');    
});
  

}
