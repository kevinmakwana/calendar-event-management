# Calendar Event Management API Documentation

## Base URL

`https://api.calendar-event-management.test/api`

## Authentication

No authentication is required for these endpoints.

## **Listing of User Events**

##### **Request**

* **Method**:  GET
* **Endpoint**: `/events`
* **Request Headers**

  * Accept : application/json
* ****Query Params:****

  * user_id **:** {user_id} (integer, required): The ID of the user.
* **Description:** Retrieves a list of events for a specific user.

##### **Response**

```
{
    "data": [
        {
            "id": 11,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-01T01:55:03+00:00",
            "end": "2024-08-01T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 12,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-02T01:55:03+00:00",
            "end": "2024-08-02T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 13,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-03T01:55:03+00:00",
            "end": "2024-08-03T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 14,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-04T01:55:03+00:00",
            "end": "2024-08-04T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 15,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-05T01:55:03+00:00",
            "end": "2024-08-05T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 16,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-06T01:55:03+00:00",
            "end": "2024-08-06T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 17,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-07T01:55:03+00:00",
            "end": "2024-08-07T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        }
    ],
    "links": {
        "first": "https://api.calendar-event-management.test/api/events?page=1",
        "last": "https://api.calendar-event-management.test/api/events?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "links": [
            {
                "url": null,
                "label": "« Previous",
                "active": false
            },
            {
                "url": "https://api.calendar-event-management.test/api/events?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next »",
                "active": false
            }
        ],
        "path": "https://api.calendar-event-management.test/api/events",
        "per_page": 15,
        "to": 7,
        "total": 7
    }
}
```

## Listing of user events specific datetime range

##### **Request**

* **Method**:  GET
* **Endpoint**: `/events`
* **Request Headers**

  * Accept : application/json
* **Query Params:**

  * user_id 	: {user_id} (integer, required): The ID of the user.
  * start 	: 2024-07-28 00:00:00 (datetime, required): The start datetime of the range.
  * end		: 2024-08-04 17:12:15 (datetime, required): The end datetime of the range.
* **Description:** Retrieves a list of events for a specific user within a given datetime range.

##### Response

```
{
    "data": [
        {
            "id": 11,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-01T01:55:03+00:00",
            "end": "2024-08-01T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 12,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-02T01:55:03+00:00",
            "end": "2024-08-02T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 13,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-03T01:55:03+00:00",
            "end": "2024-08-03T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 14,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-04T01:55:03+00:00",
            "end": "2024-08-04T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 15,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-05T01:55:03+00:00",
            "end": "2024-08-05T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 16,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-06T01:55:03+00:00",
            "end": "2024-08-06T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        },
        {
            "id": 17,
            "user_id": 1,
            "user": "Torrance Wolf",
            "title": "Sample Event",
            "description": "This is a sample event",
            "start": "2024-08-07T01:55:03+00:00",
            "end": "2024-08-07T02:55:03+00:00",
            "recurring_pattern": true,
            "frequency": "daily",
            "repeat_until": "2024-08-07T22:55:03+00:00",
            "created_at": "2024-07-31T06:04:13.000000Z",
            "updated_at": "2024-07-31T06:04:13.000000Z"
        }
    ],
    "links": {
        "first": "https://api.calendar-event-management.test/api/events?page=1",
        "last": "https://api.calendar-event-management.test/api/events?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "links": [
            {
                "url": null,
                "label": "« Previous",
                "active": false
            },
            {
                "url": "https://api.calendar-event-management.test/api/events?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next »",
                "active": false
            }
        ],
        "path": "https://api.calendar-event-management.test/api/events",
        "per_page": 15,
        "to": 7,
        "total": 7
    }
}
```

## Create User Event

##### **Request**

* **Method**:  POST
* **Endpoint**: `/events`
* **Request Headers**
  * Accept : application/json
* **Description:** Creates a new event for a user.

**Request** **Body**:

```json
{
    "user_id": 1,
    "title": "Sample Event",
    "description": "This is a sample event",
    "start": "2024-08-02T02:05:03+00:00",
    "end": "2024-08-03T02:55:03+00:00",
    "recurring_pattern": false,
    "frequency": null,
    "repeat_until": null
}
```

##### **Responses**:

201 Created.

```json
{
    "data": {
        "id": 17,
        "user_id": 1,
        "user": "Nestor Medhurst",
        "title": "Sample Event",
        "description": "This is a sample event",
        "start": "2024-08-01T21:55:03+00:00",
        "end": "2024-08-02T22:55:03+00:00",
        "recurring_pattern": false,
        "frequency": null,
        "repeat_until": null,
        "created_at": "2024-07-31T05:16:09.000000Z",
        "updated_at": "2024-07-31T05:16:09.000000Z"
    },
    "message": "Event created successfully.",
    "status": 201
}
```

**Request Body**:

```json
{
    "user_id": null,
    "title": "",
    "description": "",
    "start": "",
    "end": "",
    "recurring_pattern": false,
    "frequency": null,
    "repeat_until": null
}
```

**422 Unprocessable Entity:** Validation errors

```json
{
    "message": "The user id field is required. (and 4 more errors)",
    "errors": {
        "user_id": [
            "The user id field is required."
        ],
        "title": [
            "The title is required."
        ],
        "description": [
            "The description field is required."
        ],
        "start": [
            "The start date is required."
        ],
        "end": [
            "The end date is required."
        ]
    }
}
```

## Create Recurring User Event

##### **Request**

* **Method**:  POST
* **Endpoint**: `/events`
* **Request Headers**
  * Accept : application/json
* **Description:** Creates a new event for a user.
* **Request** **Body**:

```json
{
    "user_id": 1,
    "title":"Sample Event",
    "description":"This is a sample event",
    "start":"2024-08-01T01:55:03+00:00",
    "end":"2024-08-01T02:55:03+00:00",
    "recurring_pattern":true,
    "frequency":"daily",
    "repeat_until":"2024-08-07T22:55:03+00:00"
}
```

##### **Response**

```json
{
    "data": {
        "id": 11,
        "user_id": 1,
        "user": "Torrance Wolf",
        "title": "Sample Event",
        "description": "This is a sample event",
        "start": "2024-08-08T01:55:03.000000Z",
        "end": "2024-08-01T02:55:03.000000Z",
        "recurring_pattern": true,
        "frequency": "daily",
        "repeat_until": "2024-08-07T22:55:03+00:00",
        "created_at": "2024-07-31T06:04:13.000000Z",
        "updated_at": "2024-07-31T06:04:13.000000Z"
    },
    "message": "Event created successfully.",
    "status": 201
}
```

**Request Body**:

```json
{
    "user_id": null,
    "title": "",
    "description": "",
    "start": "",
    "end": "",
    "recurring_pattern": false,
    "frequency": null,
    "repeat_until": null
}
```

**422 Unprocessable Entity:** Validation errors

```json
{
    "message": "The user id field is required. (and 4 more errors)",
    "errors": {
        "user_id": [
            "The user id field is required."
        ],
        "title": [
            "The title is required."
        ],
        "description": [
            "The description field is required."
        ],
        "start": [
            "The start date is required."
        ],
        "end": [
            "The end date is required."
        ]
    }
}
```

## Update Event

##### **Request**

* **Method**:  PUT
* **Endpoint**: `/events/{id}/{users}/{user}`
* **Request Headers**
  * Accept : application/json
* **Description:** Update an existing user event.

**200 OK**

**
    Request Body:**

```json
{
    "title":"Sample Update Event",
    "description":"This is a sample event",
    "start":"2024-07-31T21:55:03+00:00",
    "end":"2024-08-01T22:55:03+00:00",
    "recurring_pattern":false,
    "frequency": null,
    "repeat_until":null
}
```

##### **Response:**

```json
{
    "data": {
        "id": 11,
        "user_id": 1,
        "user": "Juanita Connelly",
        "title": "Sample Event",
        "description": "This is a sample event",
        "start": "2024-07-31T21:55:03+00:00",
        "end": "2024-08-01T22:55:03+00:00",
        "recurring_pattern": false,
        "frequency": "0",
        "repeat_until": null,
        "created_at": "2024-07-31T05:32:21.000000Z",
        "updated_at": "2024-07-31T05:56:21.000000Z"
    },
    "message": "Event updated successfully.",
    "status": 200
}
```

**422 Unprocessable Entity**: Validation errors

**
    Request Body:**

```json
{
    "title":"",
    "description":"",
    "start":"",
    "end":"",
    "recurring_pattern":null,
    "frequency": null,
    "repeat_until":null
}
```

**
    Response**: 

```json
{
    "message": "The title is required. (and 4 more errors)",
    "errors": {
        "title": [
            "The title is required."
        ],
        "description": [
            "The description field is required."
        ],
        "start": [
            "The start date is required."
        ],
        "end": [
            "The end date is required."
        ],
        "recurring_pattern": [
            "The recurring pattern is required."
        ]
    }
}
```

## Update Recurring User Event

##### **Request**

* **Method**:  PUT
* **Endpoint**: `/events/{id}/{users}/{user}`
* **Request Headers**
  * Accept : application/json
* **Description:** Update an existing user event.

**Request Body:**

```json
{
    "user_id": 1,
    "title":"Sample Event",
    "description":"This is a sample event",
    "start":"2024-08-01T01:55:03+00:00",
    "end":"2024-08-01T02:55:03+00:00",
    "recurring_pattern":true,
    "frequency":"daily",
    "repeat_until":"2024-08-07T22:55:03+00:00"
}
```

##### **Responses:**

* **200 OK:**

```json
{
    "data": {
        "id": 11,
        "user_id": 1,
        "user": "Torrance Wolf",
        "title": "Sample Event",
        "description": "This is a sample event",
        "start": "2024-08-08T01:55:03.000000Z",
        "end": "2024-08-01T02:55:03.000000Z",
        "recurring_pattern": true,
        "frequency": "daily",
        "repeat_until": "2024-08-07T22:55:03+00:00",
        "created_at": "2024-07-31T06:04:13.000000Z",
        "updated_at": "2024-07-31T06:04:13.000000Z"
    },
    "message": "Event created successfully.",
    "status": 201
}
```

**Request Body:**

```json
{
    "title":"",
    "description":"",
    "start":"",
    "end":"",
    "recurring_pattern":null,
    "frequency": null,
    "repeat_until":null
}
```

**Response**:

* 422 Unprocessable Entity: Validation errors

```json
{
    "message": "The title is required. (and 4 more errors)",
    "errors": {
        "title": [
            "The title is required."
        ],
        "description": [
            "The description field is required."
        ],
        "start": [
            "The start date is required."
        ],
        "end": [
            "The end date is required."
        ],
        "recurring_pattern": [
            "The recurring pattern is required."
        ]
    }
}
```

## Delete User Event

##### **Request**

* **Method:** DELETE
* **URL:** `/events/{event_id}/user/{user_id}`
* **Headers:**
  * Accept: application/json
* **Description:** Deletes an existing event for a user.

##### Response

    No content - 204

## Delete Subsequent User Event

##### Request

* **Method:** DELETE
* **URL:** `/events/{event_id}/user/{user_id}`
* **Headers:**
  * Accept: application/json
* **Query Parameters:**  `deleteSubsequent` (boolean, required): Whether to delete subsequent events.
* **Description:** Deletes an existing event and its subsequent events for a user.

##### Response

    No Contetn - 204
