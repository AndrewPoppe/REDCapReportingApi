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

| Field Name           | Description                                                                                                                                                | Values                                                                                                         | Example                        |
| -------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------- | ------------------------------ |
| `project_id`         | Unique identifier for the project                                                                                                                          | N/A                                                                                                            | 101                            |
| `status`             | Project status (Development, Production, etc.)                                                                                                             | `0` = Development, `1` = Production, `2` = Analysis/Cleanup, `3` = Completed, `4` = Deleted                    | 1                              |
| `online_offline`     | Project availability                                                                                                                                       | `0` = Offline, `1` = Online                                                                                    | 1                              |
| `project_name`       | Name of the project                                                                                                                                        | N/A                                                                                                            | My Project                     |
| `project_created_on` | Timestamp the project was created                                                                                                                          | N/A                                                                                                            | 12:00:00                       |
| `project_created_by` | Username of the creator                                                                                                                                    | N/A                                                                                                            | jdoe                           |
| `project_irb_number` | IRB number associated with the project                                                                                                                     | N/A                                                                                                            | 123456                         |
| `surveys`            | Whether the project has any surveys enabled                                                                                                                | `0` = No, `1` = Yes                                                                                            | 1                              |
| `econsent`           | Whether the project has any eConsent surveys enabled                                                                                                       | `0` = No, `1` = Yes                                                                                            | 0                              |
| `project_users`      | The users associated with the project, formatted as a semicolon-delimited string                                                                           | N/A                                                                                                            | jdoe;jsmith;jlo                |
| `project_design`     | The users with design rights associated with the project, formatted as a semicolon-delimited string                                                        | N/A                                                                                                            | jdoe;jsmith                    |
| `project_userrights` | The users with user rights associated with the project, formatted as a semicolon-delimited string                                                          | N/A                                                                                                            | jdoe;jsmith                    |
| `project_phostid`    | The project host ID                                                                                                                                        | N/A                                                                                                            | YOUR-REDCAP-URL.org            |
| `mosio`              | Whether the project has Mosio integration enabled                                                                                                          | `0` = No, `1` = Yes                                                                                            | 0                              |
| `twilio`             | Whether the project has Twilio integration enabled                                                                                                         | `0` = No, `1` = Yes                                                                                            | 1                              |
| `cdis`               | Whether the project has CDIS integration enabled                                                                                                           | `0` = No, `1` = Yes                                                                                            | 0                              |
| `mycap`              | Whether the project has MyCap integration enabled                                                                                                          | `0` = No, `1` = Yes                                                                                            | 1                              |
| `mobile`             | Whether the project has Mobile App integration enabled                                                                                                     | `0` = No, `1` = Yes                                                                                            | 1                              |
| `mlm`                | Whether the project has any MLM languages enabled                                                                                                          | `0` = No, `1` = Yes                                                                                            | 0                              |
| `api`                | Whether the project has any API tokens enabled                                                                                                             | `0` = No, `1` = Yes                                                                                            | 1                              |
| `em`                 | Whether the project has any External Modules enabled                                                                                                       | `0` = No, `1` = Yes                                                                                            | 1                              |
| `records`            | The number of records in the project                                                                                                                       | N/A                                                                                                            | 100                            |
| `pifname`            | For research projects, the first name of the primary investigator                                                                                          | N/A                                                                                                            | Jane                           |
| `pilname`            | For research projects, the last name of the primary investigator                                                                                           | N/A                                                                                                            | Jones                          |
| `piemail`            | For research projects, the email of the primary investigator                                                                                               | N/A                                                                                                            | jane.jones@example.com         |
| `projectnotes`       | Notes about the project                                                                                                                                    | N/A                                                                                                            | This is a sample project note. |
| `purpose`            | The purpose of the project                                                                                                                                 | `0` = Practice/Just for fun , `4` = Operational Support, `2` = Research, `3` = Quality Improvement,`1` = Other | 2                              |
| `purpose_oth`        | Other purpose details                                                                                                                                      | N/A                                                                                                            | Surveys                        |
| `researchtype___0`   | For research projects, is the project marked as 'Basic or bench research'                                                                                  | `1` = Yes, `NULL` = No                                                                                         | 1                              |
| `researchtype___1`   | For research projects, is the project marked as 'Clinical research study or trial'                                                                         | `1` = Yes, `NULL` = No                                                                                         | 1                              |
| `researchtype___2`   | For research projects, is the project marked as 'Translational research 1 (applying discoveries to the development of trials and studies in humans)'       | `1` = Yes, `NULL` = No                                                                                         | 1                              |
| `researchtype___3`   | For research projects, is the project marked as 'Translational research 2 (enhancing adoption of research findings and best practices into the community)' | `1` = Yes, `NULL` = No                                                                                         | 1                              |
| `researchtype___4`   | For research projects, is the project marked as 'Behavioral or psychosocial research study'                                                                | `1` = Yes, `NULL` = No                                                                                         | 1                              |
| `researchtype___5`   | For research projects, is the project marked as 'Epidemiology'                                                                                             | `1` = Yes, `NULL` = No                                                                                         | 1                              |
| `researchtype___6`   | For research projects, is the project marked as 'Repository (developing a data or specimen repository for future use by investigators)'                    | `1` = Yes, `NULL` = No                                                                                         | 1                              |
| `researchtype___7`   | For research projects, is the project marked as 'Other'                                                                                                    | `1` = Yes, `NULL` = No                                                                                         | 1                              |

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
