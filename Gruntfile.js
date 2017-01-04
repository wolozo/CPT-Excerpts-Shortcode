module.exports = function ( grunt ) {
  'use strict';

  require( 'time-grunt' )( grunt );

  require( 'jit-grunt' )(
    grunt, {}
  );

  grunt.initConfig(
    {

      pkg: grunt.file.readJSON( 'package.json' ),

      //------------------------------------------------------------------------------
      // Details
      //------------------------------------------------------------------------------

      opt: {
        plugin: {
          name: 'CPT Excerpts',  // Name of the plugin
          slug: 'cpt-excerpts',  // Slug of the plugin
        },
        path:   {
          css:  'assets/css',       // Path to CSS files
          scss: 'assets/scss',      // Path to Scss files
        },
      },

      //------------------------------------------------------------------------------

      // SCSS/CSS Stuff

      // // SCSS
      sass: {
        options: {
          update: true,
          style:  'expanded'
        },
        dist:    {
          files: {
            '<%= opt.path.css %>/<%= opt.plugin.slug %>.css': [ '<%= opt.path.scss %>/style.scss' ],
          }
        }
      },

      postcss: {
        styleHuman: {
          options: {
            map:        false,
            processors: [
              require( 'pixrem' )(), require( 'autoprefixer' )(
                {
                  browsers: [
                    'last 3 versions', 'ie 8', 'ie 9'
                  ]
                }
              ),
            ]
          },
          src:     '<%= opt.path.css %>/<%= opt.plugin.slug %>.css',
          dest:    '<%= opt.path.css %>/<%= opt.plugin.slug %>.css',
        },

        styleMin: {
          options: {
            map:        false,
            processors: [
              require( 'cssnano' )()
            ]
          },
          src:     '<%= opt.path.css %>/<%= opt.plugin.slug %>.css',
          dest:    '<%= opt.path.css %>/<%= opt.plugin.slug %>.min.css',
        },
      },

      // watch
      watch: {
        scss: {
          files: [
            '<%= opt.path.scss %>/**/*.scss'
          ],
          tasks: [
            'scss'
          ]
        },
      }
    }
  );

  grunt.registerTask(
    'init', [
      'string-replace:all',
    ]
  );

  grunt.registerTask(
    'scss', [
      'sass', 'postcss',
    ]
  );

  grunt.registerTask( 'default', [ 'watch' ] );

};