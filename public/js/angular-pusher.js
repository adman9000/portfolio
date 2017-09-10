/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 3);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */,
/* 1 */,
/* 2 */,
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(4);


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/**
 * Brian Woodward
 *
 * Brian Woodward <http://github.com/doowb>
 * Created and maintained by Brian Woodward
 *
 * Copyright (c) 2014 Brian Woodward.
 * Licensed under the MIT License (MIT).
 */

/**
 * This code and some of the ideas in the code are inspired by the following
 * article from "25 days of AngularJS Calendar | 2013"
 * http://www.ng-newsletter.com/advent2013/#!/day/17
 */



angular.module('doowb.angular-pusher', [])

// create a provider that loads the pusher script from a cdn
.provider('PusherService', function () {
  var scriptUrl = '//js.pusher.com/2.2/pusher.min.js';
  var scriptId = 'pusher-sdk';
  var apiKey = '';
  var initOptions = {};

  this.setPusherUrl = function (url) {
    if (url) scriptUrl = url;
    return this;
  };

  this.setOptions = function (options) {
    initOptions = options || initOptions;
    return this;
  };

  this.setToken = function (token) {
    apiKey = token || apiKey;
    return this;
  };

  // load the pusher api script async
  function createScript($document, callback, success) {
    var tag = $document.createElement('script');
    tag.type = 'text/javascript';
    tag.async = true;
    tag.id = scriptId;
    tag.src = scriptUrl;

    tag.onreadystatechange = tag.onload = function () {
      var state = tag.readyState;
      if (!callback.done && (!state || /loaded|complete/.test(state))) {
        callback.done = true;
        callback();
      }
    };

    $document.getElementsByTagName('head')[0].appendChild(tag);
  }

  this.$get = ['$document', '$timeout', '$q', '$rootScope', '$window', '$location', 'PusherEventsService', function ($document, $timeout, $q, $rootScope, $window, $location, PusherEventsService) {
    var deferred = $q.defer();
    var pusher;
    var es = PusherEventsService.connection;

    function onSuccess() {
      pusher = new $window.Pusher(apiKey, initOptions);

      // Connection Events
      pusher.connection.bind('connecting', function () {
        $rootScope.$broadcast(es.connecting);
      }).bind('connected', function () {
        $rootScope.$broadcast(es.connected);
      }).bind('unavailable', function () {
        $rootScope.$broadcast(es.unavailable);
      }).bind('failed', function () {
        $rootScope.$broadcast(es.failed);
      }).bind('disconnected', function () {
        $rootScope.$broadcast(es.disconnected);
      }).bind('state_change', function (states) {
        // states = {previous: 'oldState', current: 'newState'}
        $rootScope.$broadcast(es.connected, states);
      });
    }

    var onScriptLoad = function onScriptLoad(callback) {
      onSuccess();
      $timeout(function () {
        deferred.resolve(pusher);
      });
    };

    createScript($document[0], onScriptLoad);
    return deferred.promise;
  }];
}).factory('Pusher', ['$rootScope', '$q', 'PusherService', 'PusherEventsService', function ($rootScope, $q, PusherService, PusherEventsService) {
  return {
    subscribe: function subscribe(channelName, eventName, callback) {
      var channelDeferred = $q.defer();
      PusherService.then(function (pusher) {
        var channel = pusher.channel(channelName) || pusher.subscribe(channelName);
        channel.bind(eventName, function (data) {
          if (callback) callback(data);
          $rootScope.$broadcast(channelName + ':' + eventName, data);
          $rootScope.$digest();
        }).bind(PusherEventsService.channel.success, function () {
          $rootScope.$broadcast(PusherEventsService.channel.success);
        }).bind(PusherEventsService.channel.error, function (status) {
          $rootScope.$broadcast(PusherEventsService.channel.error, status);
          //if(status == 408 || status == 503) {
          // retry?
          //}
        });

        // Presence Channel
        if (channelName.indexOf('presence-') != -1) {
          channel.bind(PusherEventsService.presence.success, function (members) {
            var dataObj = {
              count: members.count,
              list: []
            };
            members.each(function (member) {
              // for example:
              dataObj.list.push({
                id: member.id,
                info: member.info
              });
            });
            $rootScope.$broadcast(PusherEventsService.presence.success, dataObj);
          }).bind(PusherEventsService.presence.memberAdded, function (member) {
            $rootScope.$broadcast(PusherEventsService.presence.memberAdded, member);
          }).bind(PusherEventsService.presence.memberRemoved, function (member) {
            $rootScope.$broadcast(PusherEventsService.presence.memberAdded, member);
          });
        }
        channelDeferred.resolve(channel);
      });
      return channelDeferred.promise;
    },

    unsubscribe: function unsubscribe(channelName) {
      PusherService.then(function (pusher) {
        if (pusher.channel(channelName)) {
          pusher.unsubscribe(channelName);
        }
      });
    },

    // Client Event Trigger
    trigger: function trigger(channelName, eventName, obj) {
      if (eventName.indexOf('client-') == -1) return 'Event name requires \'client-\' prefix.';

      PusherService.then(function (pusher) {
        var channel = pusher.channel(channelName);
        return channel.trigger(eventName, obj);
      });
    },

    // State of connection
    connectionState: function connectionState() {
      PusherService.then(function (pusher) {
        return pusher.connection.state;
      });
    },

    members: function members(channelName) {
      if (channelName.indexOf('presence-') == -1) return 'Requires presence channel.';
      PusherService.then(function (pusher) {
        var presenceChannel = pusher.channel(channelName);
        var arr = [];
        presenceChannel.members.each(function (member) {
          arr.push({
            id: member.id,
            info: member.info
          });
        });
        return arr;
      });
    },

    membersMe: function membersMe(channelName) {
      if (channelName.indexOf('presence-') == -1) return 'Requires presence channel.';
      PusherService.then(function (pusher) {
        var presenceChannel = pusher.channel(channelName);
        return presenceChannel.members.me;
      });
    },

    membersGet: function membersGet(channelName, memberId) {
      if (channelName.indexOf('presence-') == -1) return 'Requires presence channel.';
      PusherService.then(function (pusher) {
        var presenceChannel = pusher.channel(channelName);
        return presenceChannel.members.get(memberId);
      });
    },

    membersCount: function membersCount(channelName, memberId) {
      if (channelName.indexOf('presence-') == -1) return 'Requires presence channel.';
      PusherService.then(function (pusher) {
        var presenceChannel = pusher.channel(channelName);
        return presenceChannel.members.count;
      });
    },

    disconnect: function disconnect() {
      PusherService.then(function (pusher) {
        return pusher.disconnect();
      });
    }
  };
}]).service('PusherEventsService', [function () {
  return {
    connection: {
      connecting: 'pusher:connection:connecting',
      connected: 'pusher:connection:connected',
      unavailable: 'pusher:connection:unavailable',
      failed: 'pusher:connection:failed',
      disconnected: 'pusher:connection:disconnected',
      state_change: 'pusher:connection:state_change'
    },
    channel: {
      success: 'pusher:subscription_succeeded',
      error: 'pusher:subscription_error'
    },
    presence: {
      success: 'pusher:subscription_succeeded',
      error: 'pusher:subscription_error',
      memberAdded: 'pusher:member_added',
      memberRemoved: 'pusher:member_removed'
    }
  };
}]);

/***/ })
/******/ ]);