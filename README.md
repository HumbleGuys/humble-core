humble-core

## WordPress 404 routes

Register a custom not-found page using `Route::wp('404', $handler)`. When no
WordPress route matches, the router sets the main query to 404, sends the 404
status and no-cache headers, and resolves this registered route. If no 404 route
is registered, it returns a plain `Not Found` response with status 404.

Controller and rendering exceptions still go through the server-error handler.
