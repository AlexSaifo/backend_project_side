# APIs
## **Register**
> - Route: /register
> - Route name: home.register
> - Preconditions: This route expect you to send all information needed to create a new expert or user
> - Response: This route will send you a response with a status code 200 (ok) , a token and a success message.
> - Request:
>> - Expert:
>>>- name
>>> - email
>>> - password
>>> - phone
>>> - address
>>> - skills
>>> - wallet
>>> - cost
>>> - days {Monday, Tuesday, Saturday, Friday, Thursday, Wednesday, Sunday}
>>> - consultings { Medical Consultings, Professional Consultings, Psychological Consultings, Family Consultings, Business / management Consultings }
>>> - start_day
>>> - end_day
>>> - profile_picture {png,jpg,bmp,jpeg}
>>> - is_expert {1}
>> - User:
>>>- name
>>> - email
>>> - password
>>> - phone
>>> - address
>>> - wallet
>>> - is_expert {0}
> - Response:
>> - status code:
>>> - 200 if user has been registered successfully 
>>> - 401 if any validation error occured with an error message
>> - you can find an example in the postman collection


