# Migration from V1 to V2

When updating from V1 to V2 the plugin will change some settings regarding the WooCommerce Products that have a role attached. Because of this migration we recommend to take the following steps when updating:
1. [Host] Create a tenant snapshot of your current store.
1. [Host] Create a new Version based on your current version.
1. [Host] Update the WaaS-Host plugin in the new version you created.
1. [Host] Either create a "test" tenant based on the snapshot you made on the old version and move that over to the new version, or move over your current tenant to the new version.
1. [Host] Check your storefront. But don't make any tenants yet.
1. [Client] Create a new Version and update the WaaS-Client plugin
1. [Client] Move over a tenant and check that it works.
1. [Client] Set the new version as "Production version"
1. [Host] Use your storefront to create a new tenant.
1. [Client] Move over the rest of the tenants and clean up the old version.
1. [Host] If applicable, move over the actual store to the new version, remove the Tenant Snapshot and remove the old version.
