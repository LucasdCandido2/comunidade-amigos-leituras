kitsune
Kitsu API DocsIntroductionKitsu is a modern anime discovery platform that helps you track the anime you're watching, discover new anime and socialize with other fans.With the Kitsu API you can do everything the client can do and much more.Base API path: https://kitsu.io/api/edgeJSON:APIThe Kitsu API implements the JSON:API specification. This means there are some notable semantics to how you consume it, but understanding it will take a lot of the work of using it out of your hands.These docs will include a short overview of the capabilities, but you can consult the JSON:API Specification for more information.You can be more specific about the data you want to retrieve by using URL parameters and are outlined below.Note: This documentation will display parameters with brackets ([ and ]) for readability, but actual URLs will need to be percent-encoded (%5B and %5D).Request HeadersAs per the JSON:API specification, all requests to the API should contain these headers:Accept: application/vnd.api+json
Content-Type: application/vnd.api+json
Filtering and SearchFiltering lets you query data that contains certain matching attributes or relationships. These take the form of filter[attribute]=value. For example, you can request all the anime of the Adventure category:/anime?filter[categories]=adventure
For some models, you can also search based on the query text:/anime?filter[text]=cowboy%20bebop
PaginationYou can choose how much of a resource to receive by specifying pagination parameters. Pagination is supported via limit and offset. Resources are paginated in groups of 10 by default and can be increased to a maximum of 20./anime?page[limit]=5&page[offset]=0The response will include URLs for the first, next and last page of resources in the links object based on your request."links": {
    "first": "https://kitsu.io/api/edge/anime?page[limit]=5&page[offset]=0",
    "next": "https://kitsu.io/api/edge/anime?page[limit]=5&page[offset]=5",
    "last": "https://kitsu.io/api/edge/anime?page[limit]=5&page[offset]=12062"
}
SortingSorting by attributes is also supported. By default, sorts are applied in ascending order. You can request a descending order by prepending - to the parameter. You can use a comma-delimited list to sort by multiple attributes./users?sort=-followersCount,-followingCountIncludesYou can include related resources with include=[relationship]. You can also specify successive relationships using a .. A comma-delimited list can be used to request multiple relationships./anime?include=categories,mediaRelationships.destinationSparse FieldsetsYou can request a resource to only return a specific set of fields in its response. For example, to only receive a user's name and creation date:/users?fields[users]=name,createdAtClient ImplementationsJSON:API has a great advantage in that since its standardised, API-agnostic tools can be made to abstract away the semantics of consuming and working with the data. It is recommended that you use a JSON:API client to implement the Kitsu API for this reason.Many implementations in over 13 languages can be found on the JSON:API website.AuthenticationKitsu uses OAuth 2 for authentication. Authentication is not required for most public-facing GET endpoints.It is advised to use an OAuth2 client for the language you're using, however it is not required.NOTE: NSFW/R18 content (feed posts, media, categories etc.) are hidden for all unauthenticated requests and for accounts that have NSFW content disabled in their settings.Base OAuth2 path: https://kitsu.io/api/oauthRequest HeadersOAuth does not use the JSON:API headers, instead one of the following headers are required:
Header	json	x-www-form-urlencoded
Content-Type	application/json	application/x-www-form-urlencoded
App RegistrationAfter registering your app, you will receieve a client ID and a client secret. The client ID is considered public information and is used to build login URLs or included in source code. The client secret must be kept confidential.NOTE: Application registration has not yet been implemented. For now, client_id and client_secret can be omitted, provided as empty strings or with the following:CLIENT_ID: dd031b32d2f56c990b1425efe6c42ad847e7fe3ab46bf1299f05ecd856bdb7dd
CLIENT_SECRET: 54d7307928f63414defd96399fc31ba847961ceaecef3a5fd93144e960c0e151
Grant TypesOAuth 2 provides several grant types for different use cases. The grant types defined are:Authorization Code for apps running on a web server, browser-based and mobile apps (not yet implemented)Password for logging in with a username and PasswordClient Credentials for application access (not yet implemented)Obtaining an Access TokenPassword GrantSend a POST request to https://kitsu.io/api/oauth/token with the following body:application/json{
  grant_type: 'password',
  username: '<email|slug>',
  password: '<password>' // RFC3986 URl encoded string
}
application/x-www-form-urlencodedgrant_type=password&username=<email|slug>&password=<password>
IMPORTANT: If you use x-www-form-urlencoded, you must URL encode the password field using the RFC3986 encoding scheme.Refreshing an Access TokenSend a POST request to https://kitsu.io/api/oauth/token with the following body:NOTE: If the token was issued using a client_secret then the client_id and client_secret parameters must be provided.application/json{
  grant_type: 'refresh_token',
  refresh_token: '<refresh_token>'
}
application/x-www-form-urlencodedgrant_type=refresh_token&refresh_token=<refresh_token>
Using an Access TokenOnce you've obtained the access_token using one of the grant types, you can add the following header to all API requests:Authorization: Bearer <access_token>
Access Token ResponsesSuccessful ResponseIf the request for an access token is valid, the server will respond with the following data:{
  access_token: 'abc123', // Token used in Authorization header
  created_at: 1518235801,
  expires_in: 2591963, // Seconds until the access_token expires (30 days default)
  refresh_token: '123abc', // Token used to get a new access_token
  scope: 'public',
  token_type: 'bearer'
}
Unsuccessful ResponseIf the access token request is invalid, the server will respond with one of six errors in the following format:{
  error: 'invalid_request',
  error_description: '<reason_why>'
}
These six errors are:
Error	Status	Explanation
invalid_request	400	The request is missing a parameter, uses an unsupported parameter or repeats a parameter.
invalid_client	401	The request contains an invalid client ID or secret.
invalid_grant	400	The authorization code (or password with the password grant) is invalid or expired.
invalid_scope	400	The request contains an invalid scope (password or client credential grants).
unauthorized_client	400	The client is not authorized to use the requested grant type.
unsupported_grant_type	400	The grant type requested is not recognized by the server.
HTTP Methods
Method	Description
GET	Fetch - retrieve resources
POST	Create - create new resources
PATCH	Update - (partially) modify existing resources
DELETE	Delete - remove resources
Status Codes
Code	Description
200	OK - request succeeded (GET, PATCH, DELETE)
201	Created - new resource created (POST)
204	No Content - request succeeded (DELETE)
400	Bad Request - malformed request
401	Unauthorized - invalid or no authentication details provided
404	Not Found - resource does not exist
406	Not Acceptable - invalid Accept header
5xx	Server Error
3rd Party TutorialsYou and your Kitsu Anime libraryQuestions?If you have any questions you can:Join our Discord server (recommended)Ping @wopian, @matthewdias or @nuck on Kitsu.
ReferenceAnime
AnimeAttributes
createdAt
string
ISO 8601 date and time

2013-02-20T16:00:13.609Z
updatedAt
string
ISO 8601 of last modification

2017-12-20T00:00:09.270Z
slug
string
cowboy-bebop
synopsis
string
In the year 2071, humanity has colonoized several of the planets and moons...
coverImageTopOffset
number
Deprecated

400
titles
object
canonicalTitle
string
Cowboy Bebop
abbreviatedTitles
array
averageRating
string
88.55
ratingFrequencies
object
userCount
number
43409
favoritesCount
number
3485
startDate
string
YYYY-MM-DD date

1998-04-03
endDate
string
YYYY-MM-DD date

1999-04-24
popularityRank
number
10
ratingRank
number
10
ageRating
enum
ageRatingGuide
string
17+ (violence & profanity)
subtype
enum
status
enum
tba
string
posterImage
object
coverImage
object
episodeCount
number
26
episodeLength
number
Length of episode in minutes

25
youtubeVideoId
string
ID of a youtube trailer

qig4KOK2R2g
showType
enum
nsfw
boolean
NSFW media requires authentication

false
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
season	winter, spring, summer, fall	
seasonYear	2017, 2005,2006	
streamers	Crunchyroll	
ageRating	G, PG,R,R18	
Fetch Collection
Fetch Resource
EpisodesAttributes
createdAt
string
ISO 8601 date and time

2013-02-20T18:20:39.003Z
updatedAt
string
ISO 8601 of last modification

2014-11-18T16:20:14.856Z
titles
object
canonicalTitle
string
The $$60 Billion Man
seasonNumber
number
1
number
number
1
relativeNumber
number
1
synopsis
string
There are many rumors circulating...
airdate
string
YYYY-MM-DD date

1998-04-01
length
string
length of episode in minutes

thumbnail
object
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
mediaId		DEPRECATED - use anime_id, manga_id or drama_id
mediaType	anime, anime,manga	DEPRECATED - use kind
number		
Fetch Collection
Fetch Resource
Trending AnimeAuthorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Manga
MangaAttributes
createdAt
string
ISO 8601 date and time

2013-12-18T13:59:39.232Z
updatedAt
string
ISO 8601 of last modification

2017-12-24T00:00:19.819Z
slug
string
shingeki-no-kyojin
synopsis
string
Several hundred years ago...
coverImageTopOffset
number
Deprecated

112
titles
object
canonicalTitle
string
Attack on Titan
abbreviatedTitles
array
averageRating
string
82.47
ratingFrequencies
object
userCount
number
7137
favoritesCount
number
758
startDate
string
YYYY-MM-DD date

2009-09-09
endDate
string
YYYY-MM-DD date

popularityRank
number
2
ratingRank
number
138
ageRating
enum
ageRatingGuide
string
Horror
subtype
enum
status
enum
tba
string
posterImage
object
coverImage
object
chapterCount
number
volumeCount
number
0
serialization
string
Bessatsu Shounen Magazine
mangaType
enum
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
chapterCount		
Fetch Collection
Fetch Resource
ChaptersAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
titles
object
canonicalTitle
string
Mission 01: Magical Five
volumeNumber
number
1
number
number
Absolute chapter number

1
synopsis
string
published
string
YYYY-MM-DD date

2015-06-25
length
string
Number of pages in chapter

80
thumbnail
object
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
mangaId	2, 8,987	
number		
Fetch Collection
Fetch Resource
Trending MangaAuthorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Categories
CategoriesAttributes
createdAt
string
ISO 8601 date and time

2017-05-31T06:38:31.311Z
updatedAt
string
ISO 8601 of last modification

2017-05-31T06:39:36.873Z
title
string
Elf
description
string
The origin of Elves (Norse; álfar(elves), álfr(elf)) can be found in ancient Norse mythology...
totalMediaCount
number
40
slug
string
elf
nsfw
boolean
NSFW categories requires authentication

false
childCount
number
0
image
object
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
parentId	1	
slug		
nsfw	true, false	
PaginationThe maximum page limit is unlimited for this resource.
Fetch Collection
Fetch Resource
Category FavoritesAttributes
createdAt
string
ISO 8601 date and time

2017-05-31T13:32:16.323Z
updatedAt
string
ISO 8601 of last modification

2017-05-31T13:32:16.323Z
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
userId	42603, 2,7	
categoryId		
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Media Relations
Media RelationshipsAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
role
enum
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
role		Values of the role attribute
sourceType		
sourceId		
destinationId		
Fetch Collection
Fetch Resource
MappingsAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
externalSite
enum
externalId
string
31608
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
externalSite	myanimelist/anime	
externalId		
Fetch Collection
Fetch Resource
FranchisesAttributes
createdAt
string
ISO 8601 date and time

2013-07-07T07:44:54.484Z
updatedAt
string
ISO 8601 of last modification

2013-07-07T07:44:54.484Z
titles
object
canonicalTitle
string
Status: Deprecated. Use media-relationships instead.Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
InstallmentsAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
tag
string
position
number
Status: Deprecated. Use media-relationships instead.Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
mediaType	anime, anime,manga	DEPRECATED - use kind
mediaId		DEPRECATED - use anime_id, manga_id or drama_id
Fetch Collection
Fetch Resource
Media Follows
Media Follows
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Media AttributesAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
title
string
highTitle
string
neutralTitle
string
lowTitle
string
Status: In development.Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
slug		
PaginationThe maximum page limit is unlimited for this resource.
Fetch Collection
Fetch Resource
Media Attribute VotesAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
vote
string
1
Status: In development.Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
createdAt		
userId	42603, 2,7	
mediaId		DEPRECATED - use anime_id, manga_id or drama_id
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Streamers
StreamersAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
siteName
string
Hulu
streamingLinksCount
number
760
logo
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
PaginationThe maximum page limit is unlimited for this resource.
Fetch Collection
Fetch Resource
Streaming LinksAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
url
string
http://www.hulu.com/hacklegend-of-the-twilight
subs
array
dubs
array
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
Users
BlocksAttributes
createdAt
string
ISO 8601 date and time

2017-09-23T00:00:37.565Z
updatedAt
string
ISO 8601 of last modification

2017-09-23T00:00:37.565Z
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	Y	N	Y
Yes	Admin	N	N	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
user		
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
FavoritesAttributes
createdAt
string
ISO 8601 date and time

2013-09-06T17:25:17.578Z
updatedAt
string
ISO 8601 of last modification

2017-02-05T14:15:58.435Z
favRank
number
10
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
userId	42603, 2,7	
itemType	Media, Character, Person	
itemId		
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
FollowsAttributes
createdAt
string
ISO 8601 date and time

2013-05-05T18:10:52.800Z
updatedAt
string
ISO 8601 of last modification

2013-05-05T18:10:52.800Z
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
follower		
followed		
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Linked AccountsAttributes
createdAt
string
ISO 8601 date and time

2017-08-22T20:36:46.580Z
updatedAt
string
ISO 8601 of last modification

2017-08-22T20:36:46.580Z
kind
string
my-anime-list
externalUserId
string
synthtech
token
object
shareTo
boolean
false
shareFrom
boolean
false
syncTo
boolean
true
disabledReason
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	N	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
userId	42603, 2,7	
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Profile Link SitesAttributes
createdAt
string
ISO 8601 date and time

2017-01-11T03:41:35.005Z
updatedAt
string
ISO 8601 of last modification

2017-02-09T00:03:02.975Z
name
string
Twitter
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
PaginationThe maximum page limit is unlimited for this resource.
Fetch Collection
Fetch Resource
Profile LinksAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
url
string
https://github.com/vevix
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
RolesAttributes
createdAt
string
ISO 8601 date and time

2016-12-12T14:32:06.707Z
updatedAt
string
ISO 8601 of last modification

2016-12-12T14:32:06.707Z
name
string
admin
resourceId
string
resourceType
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
StatsAttributes
createdAt
string
ISO 8601 date and time

2017-07-28T02:14:07.389Z
updatedAt
string
ISO 8601 of last modification

2017-10-20T19:07:02.241Z
kind
enum
statsData
object
Status: In development.Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	N	N	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
userId	42603, 2,7	
Fetch Collection
Fetch Resource
Delete Resource
User RolesAttributes
createdAt
string
ISO 8601 date and time

2016-12-12T14:32:06.707Z
updatedAt
string
ISO 8601 of last modification

2016-12-12T14:32:06.707Z
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	N	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
Create Resource
Delete Resource
UsersAttributes
createdAt
string
ISO 8601 date and time

2013-02-20T16:21:01.289Z
updatedAt
string
ISO 8601 of last modification

2017-06-06T20:17:07.447Z
name
string
vikhyat
pastNames
array
slug
string
vikhyat
about
string
Max length of 500 characters

Co-founder of Hummingbird. Obsessed with Gumi.
location
string
Seattle, WA
waifuOrHusbando
string
Waifu
followersCount
number
1716
followingCount
number
2031
lifeSpentOnAnime
number
Deprecated, use the stats relationship

45474
birthday
string
gender
string
commentsCount
number
0
favoritesCount
number
4
likesGivenCount
number
34
reviewsCount
number
0
likesReceivedCount
number
7
postsCount
number
1
ratingsCount
number
0
mediaReactionsCount
number
0
proExpiresAt
string
2015-01-30T16:49:35.173Z
title
string
profileCompleted
boolean
Completed profile onboarding

false
feedCompleted
boolean
Completed feeds onboarding

true
website
string
Deprecated, use the profileLinks relationship

http://twitter.com/vikhyatk
proTier
string
pro
avatar
object
coverImage
object
email
string
Logged in user only

password
string
Logged in user only. Used to set new password, always displays null

confirmed
boolean
Logged in user only. Email confirmed

true
previousEmail
string
Logged in user only

language
string
Logged in user only

timeZone
string
Logged in user only

country
string
Logged in user only.

US
shareToGlobal
boolean
Logged in user only

true
titleLanguagePreference canonical
enum
sfwFilter
boolean
Logged in user only. Toggle visibility of NSFW media and posts

false
ratingSystem
enum
theme
enum
facebookId
string
Logged in user only

hasPassword
boolean
Logged in user only

true
status
string
registered
subscribedToNewsletter
boolean
true
aoPro
string
Logged in user only. Aozora user imports that had Aozora Pro - treated as Kitsu Pro

Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	Y	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
slug		Get a user by slug (case insensitive)
name		Get a user by display name (non-unique)
self	true	Get the currently logged in user
query		Search resource
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
User Libraries
Library EntriesAttributes
createdAt
string
ISO 8601 date and time

2013-03-03T23:07:42.713Z
updatedAt
string
ISO 8601 of last modification

2016-01-15T05:53:48.037Z
status
enum
progress
number
Current episode or chapter

13
volumesOwned
number
Manga only

0
reconsuming
boolean
false
reconsumeCount
number
0
notes
string
private
boolean
false
reactionSkipped
enum
progressedAt
string
ISO 8601 of last chapter/episode change

2016-01-15T05:53:48.037Z
startedAt
string
ISO 8601 of when the user consumed the first chapter/episode

finishedAt
string
ISO 8601 of when the user consumed the last chapter/episode

2016-01-15T05:53:48.037Z
rating
string
Deprecated in favour of ratingTwenty

3.5
ratingTwenty
number
2,3..20 rating scale, displayed as 1,1.5..10

14
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y*	N	N	N
Yes	None	Y*†	Y	Y	Y
Yes	Admin	Y*	N	N	N
* Resources with private: true are not accessible, unless authenticated as the user associated with the library entry† Adult content will only be shown if enabled in the authenticated user's settingsFiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
animeId	1, 43,2,300	
dramaId		
following		
kind	anime, anime,manga	Filter by the related media type
mangaId	2, 8,987	
mediaId		DEPRECATED - use anime_id, manga_id or drama_id
mediaType	anime, anime,manga	DEPRECATED - use kind
since	2017-01-28, 2013-03-25T00:00:00.000Z	Returns entries updated since the supplied ISO 8601 date
status	current, onHold	Values of the status attribute
title		Search resource
userId	42603, 2,7	
PaginationThe maximum page limit is 500 for this resource.
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Library Entry LogsAttributes
createdAt
string
ISO 8601 date and time

2017-09-23T00:00:37.565Z
updatedAt
string
ISO 8601 of last modification

2017-09-23T00:00:38.677Z
progress
number
8
rating
number
14
reconsumeCount
number
0
reconsuming
boolean
false
status
enum
volumesOwned
number
0
actionPerformed
enum
syncStatus
enum
errorMessage
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
linkedAccountId		
syncStatus	pending, success, error	
PaginationThe maximum page limit is unlimited for this resource.
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Library EventsAttributes
createdAt
string
ISO 8601 date and time

2017-09-23T00:00:37.565Z
updatedAt
string
ISO 8601 of last modification

2017-09-23T00:00:37.565Z
notes
string
nsfw
boolean
false
private
boolean
false
progress
number
2
rating
number
14
reconsuming
boolean
false
reconsumeCount
number
0
volumesOwned
number
0
timeSpent
number
0
status
enum
event
enum
changedData
object
Status: In development.Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	N	N	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
userId	42603, 2,7	
createdAt		
Fetch Collection
Fetch Resource
Delete Resource
List ImportsAttributes
createdAt
string
ISO 8601 date and time

2017-08-22T20:37:04.815Z
updatedAt
string
ISO 8601 of last modification

2017-08-22T20:41:29.814Z
kind
string
my-anime-list-xml
inputText
string
strategy
enum
inputFile
object
progress
number
388
status
enum
total
number
388
errorMessage
string
errorTrace
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	Y	N	N
Yes	Admin	N	Y	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
userId	42603, 2,7	
Fetch Collection
Fetch Resource
Create Resource
Reactions
Media Reaction VotesAttributes
createdAt
string
ISO 8601 date and time

2017-07-25T09:42:00.190Z
updatedAt
string
ISO 8601 of last modification

2017-07-25T09:42:00.190Z
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	N	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
mediaReactionId	1, 24334	
userId	42603, 2,7	
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Media ReactionsAttributes
createdAt
string
ISO 8601 date and time

2017-07-25T09:40:43.455Z
updatedAt
string
ISO 8601 of last modification

2017-07-25T09:40:43.455Z
reaction
string
A well-crafted story of paradoxical pain and suffering. Not as painful however as the slow pace of chapter releases.
upVotesCount
number
4
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	N	N	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
createdAt		
upVotesCount	2, 7	
userId	42603, 2,7	
animeId	1, 43,2,300	
dramaId		
mangaId	2, 8,987	
mediaType	anime, anime,manga	DEPRECATED - use kind
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Review LikesAttributes
createdAt
string
ISO 8601 date and time

2014-01-04T06:21:44.394Z
updatedAt
string
ISO 8601 of last modification

2014-01-04T06:21:44.394Z
Status: Deprecated. Use media-reaction-votes instead.Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	N	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
reviewId	4, 14792	
userId	42603, 2,7	
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
ReviewsAttributes
createdAt
string
ISO 8601 date and time

2013-03-04T18:29:01.441Z
updatedAt
string
ISO 8601 of last modification

2016-12-19T08:01:58.532Z
content
string
The Anime \"Seinfeld\". Seriously, I love this show. It shines in it's dialog, and if you...
contentFormatted
string
<p>The Anime \"Seinfeld\". Seriously, I love this show. It shines in it's dialog, and if you...
likesCount
number
3
progress
string
rating
number
20
source
string
hummingbird
spoiler
boolean
false
Status: Deprecated. Use media-reactions instead.Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	N	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
mediaId		DEPRECATED - use anime_id, manga_id or drama_id
mediaType	anime, anime,manga	DEPRECATED - use kind
userId	42603, 2,7	
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Posts
PostsAttributes
createdAt
string
ISO 8601 date and time

2013-06-21T20:15:54.522Z
updatedAt
string
ISO 8601 of last modification

2017-06-27T14:08:22.843Z
content
string
I like cats. http://i.imgur.com/mJBJfzf.jpg
contentFormatted
string
I like cats. <a href="http://i.imgur.com/mJBJfzf.jpg" target="_blank">...
commentsCount
number
13
postLikesCount
number
2
spoiler
boolean
false
nsfw
boolean
false
blocked
boolean
false
deletedAt
string
topLevelCommentsCount
number
12
editedAt
string
targetInterest
string
embed
string
embedUrl
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y*	Y
Yes	Admin	Y	N	Y	Y
* Post owner has 30 minutes from creation to edit the resourceFiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Post LikesAttributes
createdAt
string
ISO 8601 date and time

2016-11-02T00:20:19.057Z
updatedAt
string
ISO 8601 of last modification

2016-11-02T00:20:19.057Z
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	N	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
postId	1	
userId	42603, 2,7	
Fetch Collection
Fetch Resource
Create Resource
Delete Resource
Post FollowsAttributes
createdAt
string
ISO 8601 date and time

2017-05-04T03:53:54.049Z
updatedAt
string
ISO 8601 of last modification

2017-05-04T03:53:54.049Z
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	N	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
postId	1	
userId	42603, 2,7	
Fetch Collection
Fetch Resource
Create Resource
Delete Resource
Comments
CommentsAttributes
createdAt
string
ISO 8601 date and time

2014-08-07T19:08:14.364Z
updatedAt
string
ISO 8601 of last modification

2014-08-07T19:08:14.364Z
content
string
This is a test.
contentFormatted
string
This is a test.
blocked
boolean
false
deletedAt
string
likesCount
number
0
repliesCount
number
0
editedAt
string
embed
string
embedUrl
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y*	Y
Yes	Admin	Y	N	Y	Y
* Comment owner has 30 minutes from creation to edit the resourceFiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
postId	1	
parentId	1	
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Comment LikesAttributes
createdAt
string
ISO 8601 date and time

2016-12-12T14:42:03.440Z
updatedAt
string
ISO 8601 of last modification

2016-12-12T14:42:03.440Z
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	N	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
commentId	1	
userId	42603, 2,7	
Fetch Collection
Fetch Resource
Create Resource
Delete Resource
Characters
Anime CharactersAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
role
enum
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
animeId	1, 43,2,300	
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Manga CharactersAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
role
enum
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
mangaId	2, 8,987	
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
CharactersAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
slug
string
black

jet
name
string
Jet Black
malId
number
3
description
string
Jet, known on his home satellite as the \"Black Dog\"...
image
object
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
slug		
name		
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Producers & Staff
Anime ProductionsAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
role
enum
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
animeId	1, 43,2,300	
producerId	1, 2,3	
role		Values of the role attribute
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Anime StaffAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
role
string
Role of the person (Multiple roles are comma seperated)

Producer
Status: In development. Replaces castings.Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Manga StaffAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
role
string
Role of the person (Multiple roles are comma seperated)

producer
Status: In development. Replaces castings.Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
ProducersAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
slug
string
sunrise
name
string
Sunrise
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
slug		
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
PeopleAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
image
string
https://media.kitsu.io/people/images/1/original.jpg?1416260317
name
string
Masahiko Minami
malId
number
6519
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
CastingsAttributes
createdAt
string
ISO 8601 date and time

2017-07-27T22:21:26.824Z
updatedAt
string
ISO 8601 of last modification

2017-07-27T22:47:45.129Z
role
string
Role of the person (Multiple roles are comma seperated)

producer
voiceActor
boolean
false
featured
boolean
false
language
string
Language casted in

English
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
mediaId		DEPRECATED - use anime_id, manga_id or drama_id
mediaType	anime, anime,manga	DEPRECATED - use kind
language	Japanese, English	
featured	true, false	
isCharacter	true, false	
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Groups
GroupsAttributes
createdAt
string
ISO 8601 date and time

2015-02-17T21:16:53.207Z
updatedAt
string
ISO 8601 of last modification

2017-10-26T20:51:24.215Z
slug
string
one-piece-group
about
string
Group for fans of the One Piece anime and or manga...
locale
string
membersCount
number
548
name
string
One Piece group
nsfw
boolean
false
privacy
enum
rules
string
rulesFormatted
string
leadersCount
number
2
neighborsCount
number
0
featured
boolean
false
tagline
string
lastActivityAt
string
2017-10-26T17:56:45.259Z
avatar
object
coverImage
object
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
featured	true, false	
category		
privacy	open, closed, restricted	
query		Search resource
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Group Action LogsAttributes
createdAt
string
ISO 8601 date and time

2017-03-12T16:40:59.662Z
updatedAt
string
ISO 8601 of last modification

2017-03-12T16:40:59.662Z
verb
string
about_changed
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	N	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
group		
Fetch Collection
Fetch Resource
Group BansAttributes
createdAt
string
ISO 8601 date and time

2017-03-12T16:40:59.662Z
updatedAt
string
ISO 8601 of last modification

2017-03-12T16:40:59.662Z
notes
string
notesFormatted
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	Y	N	Y
Yes	Admin	N	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
group		
Fetch Collection
Fetch Resource
Create Resource
Delete Resource
Group CategoriesAttributes
createdAt
string
ISO 8601 date and time

2017-03-12T06:24:28.884Z
updatedAt
string
ISO 8601 of last modification

2017-03-12T06:24:28.884Z
name
string
Anime & Manga
slug
string
anime-manga
description
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
slug		
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Group InvitesAttributes
createdAt
string
ISO 8601 date and time

2017-03-12T16:55:27.999Z
updatedAt
string
ISO 8601 of last modification

2017-03-12T19:45:12.292Z
acceptedAt
string
2017-03-12T19:45:12.186Z
declinedAt
string
revokedAt
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	N	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
group		
sender		
user		
status	current, onHold	Values of the status attribute
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Group Member NotesAttributes
createdAt
string
ISO 8601 date and time

2017-03-12T16:55:27.999Z
updatedAt
string
ISO 8601 of last modification

2017-03-12T19:45:12.292Z
content
string
contentFormatted
string
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	N	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Group MembersAttributes
createdAt
string
ISO 8601 date and time

2015-02-17T21:16:53.211Z
updatedAt
string
ISO 8601 of last modification

2015-02-17T21:16:53.211Z
rank
enum
unreadCount
number
member's unread posts in group

16
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
rank		
group		
user		
queryGroup		
queryUser		
queryRank		
groupCategory		
groupName		
query		Search resource
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Group NeighborsAuthorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	N	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
source		
destination		
Fetch Collection
Fetch Resource
Create Resource
Delete Resource
Group PermissionsAttributes
createdAt
string
ISO 8601 date and time

2017-03-12T05:12:42.823Z
updatedAt
string
ISO 8601 of last modification

2017-03-12T05:12:42.823Z
permission
enum
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	Y	N	Y
Yes	Admin	Y	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
Fetch Collection
Fetch Resource
Create Resource
Delete Resource
Group ReportsAttributes
createdAt
string
ISO 8601 date and time

2017-03-12T05:12:42.823Z
updatedAt
string
ISO 8601 of last modification

2017-03-12T05:12:42.823Z
reason
enum
status
enum
explanation
string
naughtyType
string
Comment
naughtyId
number
1
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	Y	Y	N
Yes	Admin	N	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
group		
user		
naughty		
naughtyType	Post, Comment, Review, Reaction	
reason	nsfw, spoiler	
status	current, onHold	Values of the status attribute
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Group Ticket MessagesAttributes
createdAt
string
ISO 8601 date and time

2017-06-16T09:11:46.241Z
updatedAt
string
ISO 8601 of last modification

2017-06-16T09:11:46.241Z
kind
enum
content
string
Could u please add this recap episode to the series...
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	N	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
ticket		
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Group TicketsAttributes
createdAt
string
ISO 8601 date and time

2017-06-16T09:11:46.042Z
updatedAt
string
ISO 8601 of last modification

2017-07-24T20:02:51.697Z
status
enum
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	Y	Y	N
Yes	Admin	N	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
group		
user		
assignee		
status	current, onHold	Values of the status attribute
queryGroup		
query		Search resource
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Leader Chat MessagesAttributes
createdAt
string
ISO 8601 date and time

2017-03-12T17:23:36.640Z
updatedAt
string
ISO 8601 of last modification

2017-03-12T17:23:36.640Z
content
string
Does this work?
contentFormatted
string
<p>Does this work?</p>\n
editedAt
string
2017-03-12T17:23:36.640Z
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	Y	Y	Y	Y
Yes	Admin	N	N	N	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
groupId		
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Reports
ReportsAttributes
createdAt
string
ISO 8601 date and time

2017-05-04T03:53:54.049Z
updatedAt
string
ISO 8601 of last modification

2017-05-04T03:53:54.049Z
reason
enum
status
enum
explanation
string
naughtyType
string
Comment
naughtyId
number
28435305
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	N	N	N	N
Yes	None	N	Y	Y	N
Yes	Admin	Y	N	Y	N
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation
Filter	Example	Notes
userId	42603, 2,7	
naughtyId		
naughtyType	Post, Comment, Review, Reaction	
status	current, onHold	Values of the status attribute
reason	nsfw, spoiler	
Fetch Collection
Fetch Resource
Create Resource
Update Resource
Delete Resource
Site Announcements
Site AnnouncementsAttributes
createdAt
string
ISO 8601 date and time

2017-05-12T02:15:29.309Z
updatedAt
string
ISO 8601 of last modification

2017-05-12T02:15:29.309Z
title
string
It's update time!
description
string
A new release is upon us. Featuring mostly bug fixes but a few quality of life tweaks!
imageUrl
string
https://media.giphy.com/media/3og0IEXRvwMN0cLSaQ/giphy.gif
link
string
https://medium.com/heykitsu/kitsu-release-notes-may-11th-2017-803bacc10e34
Authorisation
Authenticated	Role	GET	POST	PATCH	DELETE
No	None	Y	N	N	N
Yes	None	Y	N	N	N
Yes	Admin	Y	Y	Y	Y
FiltersFilters can be used in camelCase or snake_case format. See [Introduction/JSON:API/filtering-and-search]*https://kitsu.docs.apiary.io/#introduction/json-api/filtering-and-search) for usage documentation


