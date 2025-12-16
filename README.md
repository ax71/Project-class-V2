# ⚡️ Backend API Update Log (v2.1)

**Target:** Frontend Team  
**Status:** Live on `dev` branch  
**Last Updated:** Dec 16, 2025

---

## 🚨 Breaking Changes / Critical Notes

1.  **Material Object**:

    -   ❌ Old field: `content_url`
    -   ✅ **New field:** `file_path` (Contains the relative storage path).

2.  **Progress Logic (Auto-Calculated)**:
    -   **STOP** calculating percentages on the frontend.
    -   The backend automatically counts `Total Items (Materials + Quizzes)` vs `Completed Items`.
    -   Just send `is_completed: true` for a specific item.

---

## 🚀 Key Feature Updates

### 1. Granular Progress Tracking

We now track _exact_ items completed, not just a random percentage number.

**Endpoint:** `POST /api/update-progress`
**Payload:**

```json
{
    "course_id": 1,
    "material_id": 10, // OR "quiz_id": 5
    "is_completed": true
}
```

**Response:**

```json
{
    "message": "Progress updated",
    "data": {
        "percentage": 50, // Display this directly
        "completed_items": 5,
        "total_items": 10
    }
}
```

### 2. Fetching Progress (Two Ways)

-   **For Dashboard Cards:**
    -   **Endpoint:** `GET /api/progress-summary`
    -   **Returns:** Array of courses with `percentage`, `completed_items`, `total_items`.
-   **For Course Detail (Checklists):**
    -   **Endpoint:** `GET /api/my-progress`
    -   **Returns:** Raw list of _completed_ `material_id` and `quiz_id`. Use this to toggle green checks ✅ on your UI lists.

### 3. Certificates

-   **Logic:** Strict **100% completion** required (All Materials + All Quizzes).
-   **Endpoint:** `POST /api/my-certificates/generate`
-   **Payload:** `{ "course_id": 1 }`

---

## 🛠 API Cheat Sheet

| Verb   | Endpoint                        | Description                                      |
| :----- | :------------------------------ | :----------------------------------------------- |
| `GET`  | `/api/user`                     | Returns user data wrapped in `{ data: { ... } }` |
| `GET`  | `/api/materials`                | List materials. Response has `file_path`.        |
| `POST` | `/api/update-progress`          | Mark item as complete. Auto-updates %.           |
| `GET`  | `/api/progress-summary`         | Course list with calculated progress %.          |
| `POST` | `/api/my-certificates/generate` | Generates cert if progress == 100%.              |

---

> **Note to Frontend:** The backend is now the "source of truth" for logic. You don't need to try and calculate scores, percentages, or validation rules. Just consume the API responses. 🤝
