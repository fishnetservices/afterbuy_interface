# afterbuy_interface
Afterbuy Interface last tested on Modified eCommerce 1.06


Technische Vorraussetzungen:

Server mit einem memory_limit von mindestens 192 MB.
Wir empfehlen einen eigenen Server (kein Shared Hosting)
PHP 7.1 / 7.2
Je 1000 Produkte sollten Sie 5 Cronjobs rechnen. 3 für den Produktimport, 1 für den Abgleich der Bestellungen und 1 für den Bilderimport. Entsprechend viele Cronjobs muss Ihr Hoster erlauben.

Aktuelle (Version 1.5) Funktionen:

Importiert Kategorien von Afterbuy Level x bis Afterbuy Level y
Importiert Produkte von Afterbuy Level x bis Afterbuy Level y
Importiert Variationssets (keine Produktsets)
Importiert Hauptbild aus "Standardbilder - gross"
Importiert weitere Bilder aus "Memofeld" (getrennt mit Tabzeichen | )
Importiert Afterbuy Freifelder 1-10 als Meta Title, Meta Description oder Bestellbeschreibung (für wesentliche Artikelmerkmale)
Importiert Verkaufspreis
Importiert Händler Verkaufspreis und weist ihn gewünschten Kundengruppen zu.
Importiert Kopf-Vorlage / Fuß-Vorlage wenn gewünscht
Überschreiben vorhandener Produktbilder ja/nein
Produkte mit Menge 0 importieren ja/nein
Produkt nach Import auf aktiv oder inaktiv stellen
Beim Import folgendes Produkttemplate / Produkt Auflistung / Produkt Optionen Template / Kategorietemplate verwenden
Bestellstatus festlegen für bezahlte Bestellungen
Bestellstatus festlegen für bezahlte+versendete Bestellungen
automatischer Import über Cronjobs
manueller Import im Admin möglich (aber nicht empfohlen) über Inhalte => Import => Afterbuy Import





Technical requirements:

Server with a memory_limit of at least 192 MB.
We recommend your own server (no shared hosting)
PHP 7.1 / 7.2
You should calculate 5 cronjobs per 1000 products. 3 for product import, 1 for order matching and 1 for image import. Your hoster must allow a corresponding number of cronjobs.

Current (Version 1.5) functions:

Imports categories from Afterbuy Level x to Afterbuy Level y
Imports products from Afterbuy Level x to Afterbuy Level y
Imports variation sets (not product sets)
Imports main image from "Standard images - large
Imports additional images from "Memo field" (separated with tab characters | )
Imports Afterbuy free fields 1-10 as Meta Title, Meta Description or order description (for essential article characteristics)
Imported Sales price
Imports dealer sales price and assigns it to desired customer groups.
Imports head template / foot template if desired
Overwrite existing product images yes/no
Import products with quantity 0 yes/no
Set product to active or inactive after import
Use the following product template / product listing / product options template / category template during import
Set order status for paid orders
Set order status for paid+shipped orders
automatic import via cronjobs
manual import in admin possible (but not recommended) via Content => Import => Afterbuy Import

