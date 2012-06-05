# http://www.seancolombo.com/2010/07/28/how-to-make-and-use-a-custom-sass-function/
require 'pp'

module SpringbokFunctions
        def get_command_line_param(paramName, defaultResult="")
                assert_type paramName, :String
                retVal = defaultResult.to_s

                # Look through the args given to SASS command-line
                ARGV.each do |arg|
                        # Check if arg is a key=value pair
                        if arg =~ /.=./
                                pair = arg.split(/=/)
                                if(pair[0] == paramName.value)
                                        # Found correct param name
                                        retVal = pair[1] || defaultResult
                                end
                        end
                end
    begin
      Sass::Script::Parser.parse(retVal, 0, 0)
    rescue
      Sass::Script::String.new(retVal)
    end
        end
        
  def getVar()
    return options.get_var('variable')
  end
end

module Sass::Script::Functions
  include SpringbokFunctions
end



=begin

module Sass
  module Importers
    class SpringbokCore < Filesystem
      
      # @see Base#find_relative
      def find_relative(name, base, options)
        return _find("/var/www/springbok/core/includes/scss", name, options) if(name[0,5] == "CORE/")
      end 

      # @see Base#find
      def find(name, options)
        return nil;
      end

      # @see Base#key
      def key(name, options)
        [self.class.name + ":" + File.dirname(File.expand_path(name)),
          File.basename(name)]
      end
    end
  end
end

module Sass::Plugin
  Sass::Plugin.options[load_paths] << Sass::Importers::SpringbokCore.new
end

=end