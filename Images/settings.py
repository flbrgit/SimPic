import json
import pathlib

class SETTINGS:
    def load_settings():
        path = str(pathlib.Path(__file__).parent.absolute()) + "/static/settings.json"
        path = path.replace("Images", "")
        with open(path, "r") as file:
            data = json.load(file)
        return data
    
    def write_settings(settings):
        with open((str(pathlib.Path(__file__).parent.absolute()) + "/static/settings.json").replace("Images", ""),"w") as file:
            json.dump(settings, file)