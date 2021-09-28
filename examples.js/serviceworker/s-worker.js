/* s-worker.js
 * This is another service worker example that worked with
 * s-worker.main.php
 * NOTE: "caches" is a global read-only variable, which is an instance of CacheStorage
 */

var CACHE_NAME = 'v3';
var urlsToCache = [
                   'main.txt'
                  ];

self.addEventListener('install', (event) => {
  // Perform install steps
  // "caches" is a global read-only variable, which is an instance of CacheStorage,
  // For more info, refer to:
  // https://developer.mozilla.org/en-US/docs/Web/API/WindowOrWorkerGlobalScope/caches

  event.waitUntil(caches.open(CACHE_NAME)
                  .then((cache) => {
    console.log('Opened cache');
    return cache.addAll(urlsToCache);
  })
                 );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(caches.match(event.request)
                    .then((response) => {
    // Cache hit - return response
    if(response) {
      console.log("respondWith:", response);
      return response;
    }

    // IMPORTANT: Clone the request. A request is a stream and
    // can only be consumed once. Since we are consuming this
    // once by cache and once by the browser for fetch, we need
    // to clone the response.
    const fetchRequest = event.request.clone();

    return fetch(fetchRequest).then((response) => {
      // Check if we received a valid response
      if(!response || response.status !== 200 || response.type !== 'basic') {
        return response;
      }

      // IMPORTANT: Clone the response. A response is a stream
      // and because we want the browser to consume the response
      // as well as the cache consuming the response, we need
      // to clone it so we have two streams.
      const responseToCache = response.clone();

      caches.open(CACHE_NAME).then((cache) => {
        cache.put(event.request, responseToCache);
      });

      return response;
    });
  })
                   );
});

self.addEventListener('activate', (event) => {
  var cacheWhitelist = ['pages-cache-v1', 'blog-posts-cache-v1'];

  event.waitUntil(caches.keys().then((cacheNames) => {
    return Promise.all(cacheNames.map((cacheName) => {
      if(cacheWhitelist.indexOf(cacheName) === -1) {
        return caches.delete(cacheName);
      }
    })
                      );
  })
                 );
});
