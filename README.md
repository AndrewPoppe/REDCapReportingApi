# REDCap Reporting API

The REDCap Reporting API provides a simple and secure way to access data in your REDCap server. It is designed for easy integration with external applications and systems.

---

## API Token

To access the API, you must use an **API Token**, which acts as your authorization credential.

- Keep your token secure and do not share it.
- You can regenerate or delete your API token via the interface.

---

## API Usage

### Base URL

```
https://YOUR-REDCAP-URL.org/api/?type=module&prefix=redcap_reporting_api&page=api&NOAUTH
```

### Parameters

Only **one** of the following parameters can be used in a request:

- `report` — to access a **built-in report**
- `query` — to access a **custom SQL query** defined in the Database Query Tool

### Authorization Header

All requests must include a `Bearer` token:

```
Authorization: Bearer YOUR_API_TOKEN
```

---

## Built-In Report: Project Housekeeping

This report returns a list of all projects on the REDCap server, along with additional information such as status, creators, and associated users.

### Report name
Use the report parameter value of "project_housekeeping" to access this report.
```
https://YOUR-REDCAP-URL.org/api/?type=module&prefix=redcap_reporting_api&page=api&NOAUTH&report=project_housekeeping
```

### Return Type

Returns a JSON object containing the requested report data.

### Report Fields

| Field Name           | Type   | Description                                               | Example                   |
|----------------------|--------|-----------------------------------------------------------|---------------------------|
| `status`             | string | Project status (Development, Production, etc.)            | Development               |
| `online_offline`     | int    | Project availability (`0 = Offline`, `1 = Online`)        | 1                         |
| `project_name`       | string | Name of the project                                       | My Project                |
| `project_created_by` | string | Username of the creator                                   | jdoe                      |
| `project_phostid`     | string | Project host ID                                           | YOUR-REDCAP-URL.org |
| `project_created_on` | string | Timestamp the project was created                         | 12:00:00                  |
| `project_irb_number` | string | IRB number associated with the project                    | 123456                    |
| `project_users`      | string | Users associated with the project (semicolon-delimited)   | jdoe;jsmith               |

---

## Custom Query API

Allows access to saved SQL queries from the Database Query Tool.

### Return Structure

Returns a JSON object with the following structure:

```json
{
  "data": [ /* query results */ ],
  "query": "SQL statement"
}
```

### Available Queries

The available queries can be configured on the module configuration page.

Use the `query` parameter in the URL to access a query by its ID:

```
...&query=1
```

---

## Example Request

### Fetch the Project Housekeeping Report

```bash
curl -X GET "https://YOUR-REDCAP-URL.org/api/?type=module&prefix=redcap_reporting_api&page=api&NOAUTH&report=project_housekeeping" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### Fetch Custom Query with ID 1

```bash
curl -X GET "https://YOUR-REDCAP-URL.org/api/?type=module&prefix=redcap_reporting_api&page=api&NOAUTH&query=1" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```
