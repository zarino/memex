String.prototype.startsWith = function(str){
  return this.slice(0, str.length) == str
}

String.prototype.endsWith = function(str){
  return this.slice(-str.length) == str
}

function logIn(){
  var $input = $('input#apikey')
  var $btn = $input.next()
  console.log('log in')
}

$(function(){
  $('input#apikey').focus().on('keyup', function(e){
    if(e.which == 13){ logIn() }
  }).next().on('click', logIn)
})
