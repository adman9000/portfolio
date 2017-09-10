//This method uses angular-pusher. Probably the one to go with	
var app = angular.module('myApp', ['doowb.angular-pusher']).

config(['PusherServiceProvider',
  function(PusherServiceProvider) {
    PusherServiceProvider
    .setToken('5ebf5e7aab6cdc39c655')
    .setOptions({ cluster: 'eu',
      encrypted: true});
  }
]);

app.config(function($interpolateProvider){
    $interpolateProvider.startSymbol('[[').endSymbol(']]');
});


  