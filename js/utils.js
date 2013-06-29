String.prototype.startsWith = function(str){
  return this.slice(0, str.length) == str
}

String.prototype.endsWith = function(str){
  return this.slice(-str.length) == str
}

String.prototype.strip = function(){
  return $.trim(this)
}

window.Memex = {
  Model: {},
  Collection: {},
  View: {}
}