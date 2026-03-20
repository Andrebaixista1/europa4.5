require "sinatra"
require "webrick"

set :bind, ENV.fetch("BIND", "0.0.0.0")
set :port, ENV.fetch("PORT", 4567)
set :server, :webrick
set :public_folder, File.join(__dir__, "public")
set :views, File.join(__dir__, "views")

helpers do
  def api_base_url
    ENV.fetch("API_BASE_URL", "http://localhost:8000/api")
  end
end

get "/" do
  erb :index, locals: { api_base_url: api_base_url }
end
