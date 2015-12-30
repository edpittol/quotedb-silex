module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    cfg: {
        dist: '.'
    },
    watch: {
      files: '<%= cfg.dist %>/**/*',
      options: {
        livereload: true
      }
    }
  });

  // Load the plugins that provides the tasks.
  grunt.loadNpmTasks('grunt-contrib-watch');

};