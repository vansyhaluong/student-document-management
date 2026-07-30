# `App\Repositories`

Repository contracts and persistence implementations live below this
namespace. Use-case-specific interfaces belong in `Contracts`; Eloquent or
Query Builder implementations belong in `Eloquent`.

Repositories are the persistence boundary. They do not authorize actions,
decide business transitions, open business transactions, or depend on the HTTP
layer. Do not add a generic `BaseRepository`.
