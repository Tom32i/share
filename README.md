Family Photos
=============

Simple website to share photos with family

## Usage

Put some pictures in `web/photos`, following this structure:

```
web/
    photos/
        my-album/
            .title
            Archive.zip
            photo-01.jpg
            photo-02.jpg
            photo-03.jpg
```

- `.title` should contain the name of the event
- `*.zip` should contain all the pictures of the album in a zip archive.

### Private album

Add a `.private` file in any picture directory to hide it from the list.

The album will still be accessible at its url: `/{directory}`.

⚠️ Warning: this is not a secure way to prevent unwanted people from accessing your photos.

## Deploy the code

    make deploy@prod

## Synchronyse the picture folder

    make upload@prod
