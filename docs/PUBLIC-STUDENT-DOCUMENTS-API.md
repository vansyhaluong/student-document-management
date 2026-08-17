# Public Student Documents API

Approved JSON endpoint for mobile to list documents of one student by
`student_code`. This is not a general staff/CRUD API.

## Method and URL

```text
GET /api/students/{studentCode}/documents
```

Authentication: none. Same public lookup model as `POST /tra-cuu-ho-so`.

Throttle: `30` requests / minute (`throttle:30,1`).

## Path parameter

| Name | Rules |
| --- | --- |
| `studentCode` | required, string, max 20, `^[A-Za-z0-9-]+$` |

Input is trimmed. Casing is not rewritten.

## Example request

```http
GET /api/students/520H0001/documents
Accept: application/json
```

## Success — student exists

HTTP `200`

```json
{
    "success": true,
    "message": "Lấy dữ liệu thành công",
    "data": {
        "student_code": "520H0001",
        "student_exists": true,
        "documents": [
            {
                "document_code": "HS-2608-0001",
                "document_type": "Giấy xác nhận sinh viên",
                "status": "waiting_for_receipt",
                "status_label": "Chờ tiếp nhận",
                "submitted_at": "2026-08-17",
                "completed_at": null
            }
        ]
    }
}
```

`documents` is `[]` when the student exists but has no records.

## Success — student not found

HTTP `200`

```json
{
    "success": true,
    "message": "Lấy dữ liệu thành công",
    "data": {
        "student_code": "520H9999",
        "student_exists": false,
        "documents": []
    }
}
```

## Validation

Invalid `studentCode` returns HTTP `422`:

```json
{
    "success": false,
    "message": "Dữ liệu không hợp lệ",
    "errors": {
        "student_code": ["Mã số sinh viên không đúng định dạng."]
    }
}
```

## Public fields

Each document includes only:

- `document_code`
- `document_type`
- `status` (enum code)
- `status_label` (Vietnamese label)
- `submitted_at` (`Y-m-d`)
- `completed_at` (`Y-m-d` or `null`)

The API does not return assignment, notes, invalid reason, history, audit,
student PII, or other internal fields.

## Web lookup

`POST /tra-cuu-ho-so` is unchanged and still uses `d/m/Y` dates plus
Vietnamese status labels for Blade.
