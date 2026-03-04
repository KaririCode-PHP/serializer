# ADR-002: Attribute-Based Serialization Groups

**Status:** Accepted  
**Date:** 2026-02-28  
**Component:** KaririCode\Serializer V3.1

## Context

Different API consumers need different views of the same object. An admin endpoint should
include `internalId`; a public endpoint should not. JMS Serializer and Symfony use "groups"
for this purpose.

## Decision

The `#[Serialize]` attribute supports a `groups` parameter:

```php
#[Serialize(name: 'user_id', groups: ['admin', 'internal'])]
public string $userId;

#[Serialize(groups: ['public', 'api'])]
public string $name;
```

When serializing with groups, only properties matching at least one group are included.
Properties without groups are always included (unless `ignore: true`).

```php
$serializer->serialize($dto, 'json', ['public']);  // only 'public' group
$serializer->serialize($dto, 'json');              // all non-ignored
```

## Consequences

- Fine-grained visibility control per consumer.
- Zero runtime overhead when not using groups.
- Compatible with name-mapping and ignore features.
