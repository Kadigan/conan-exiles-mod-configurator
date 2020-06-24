# PHP+JS mod configurator

If you happen to run PHP and a webserver on your Conan: Exiles machine, you are welcome to use this handy script. It allows you to order your mod list, activate/deactivate mods, and so on.

To carry out actions, simply drag the items around - they can be dragged from the active list to the inactive list and back, and ordered around. Once done, hit 'Commit changes to the modlist' to save your changes.

The manager includes code to access the STEAM API, specifically to check last-modified times of Workshop items - if a mod requires an update, you'll see it on the list (similarily, you'll see if a mod has been pulled from STEAM).

***NOTE:* to use STEAM API integration, you need to keep the mods in their ID folders, for example:

`/Mods/1125427722/LitManItemStackAndContainerSize.pak`

The script relies on these workshop ItemIDs to fetch data about them.**
