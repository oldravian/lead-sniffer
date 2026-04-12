You are working in a Laravel + Inertia + Vue 3 project using shadcn-vue.

Goal: Add a reusable delete confirmation UI.

Rules:

- First, search the repo for an existing confirmation component (Confirm*, AlertDialog, Dialog, Modal, Delete*, etc).
- If it exists, reuse it and show me how to call it.
- If it does not exist, create a new reusable component that matches the current project conventions and shadcn-vue structure.

Project conventions:

- Reuse shadcn-vue primitives from: resources/js/components/ui/
- Put app level reusable components in: resources/js/components/
- Use Tailwind classes, do not write custom CSS unless needed.
- Prefer lucide-vue-next icons if an icon is needed.
- Keep the API simple: props for title, description, confirm_text, cancel_text, loading, and emit events confirm and cancel.

Deliverables:

1. New component file path and full code.
2. Example usage in an existing page or component where a delete action happens.
3. If any imports or registrations are needed, include them.
