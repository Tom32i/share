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

## Deploy the code

    make deploy@prod

## Synchronyse the picture folder

    make upload@prod
