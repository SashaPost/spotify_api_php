##  Project Description

This is a personal training (and hobby) web application that allows users to connect to the Spotify API using [OAuth 2.0 service](https://developer.spotify.com/documentation/web-api/tutorials/code-flow). To access the app, users are required to log in, which allows them to save unique data to the app's database. The authorization token is also saved and refreshed every time the user logs in.

Currently, the app has implemented a job queuing system using the database driver. However, for the largest playlists, the system hits the 'max_execution_time' limit due to the huge amount of data, which needs to be fixed. Once resolved, a feature to delete a song from all playlists on Spotify will be added.

Future plans for the application include adding the capability to synchronize user data with various music services, including Last.fm, Apple Music, Deezer, and more.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
