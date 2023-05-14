"""
Terminal for SimPic Browser.
"""
import os
from PIL import Image, ImageStat
import PIL
import mysql.connector
from tqdm import tqdm
import shutil
import logging

import util

logging.basicConfig(filename='browser.log', level=logging.DEBUG, filemode="w")

class TERMINAL:
    def __init__(self):
        self.path = os.getcwd()
        self.current = self.path
        self.ex = False
        self.inputs = [("exit", self.exit, "Terminates the program."),
                       ("redundant", self.manage_redundant, "Search for redundant data in the database."),
                       ("split", self.split, "Split files into separate directories."),
                       ("add_folder", self.add_folder, "Add a directory with multiple folders to Browser."),
                       ("duplicates", self.duplicates, "Search for duplicates in a given directory.")]
        self.db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="browser"
        )
        self.cursor = self.db.cursor()
        

    def run(self):
        while not self.ex:
            order = input("{}>".format(self.current.replace(self.path, "")))
            for o, f, _ in self.inputs:
                if o == order:
                    f()
            if order == "help":
                for o, _, h in self.inputs:
                    print("{:<15} {}".format(str(o)+":", h))

    def exit(self, *args, **kwargs):
        self.ex = True
        self.cursor.close()
        self.db.close()

    def manage_redundant(self, *args, **kwargs):
        print("Please choose database (objects, gallery, content)")
        r = True
        while r:
            r = input(">")
            if r == "exit":
                print("Terminating search...")
                return
            elif r in ["objects", "gallery", "content"]:
                db = r
                r = False
        sql = "SELECT * FROM `{}`".format(db)
        self.cursor.execute(sql)
        result = self.cursor.fetchone()
        delete = []
        while result is not None:
            path = result[1]
            if not os.path.exists(os.path.join(self.path, "static", path)):
                delete.append(result[0])
            result = self.cursor.fetchone()
        print("Found %s redundant entrys. Continue (J/n)?" % len(delete))
        r = True
        while r:
            r = input(">")
            if r == "exit" or r == "n":
                print("Terminating search...")
                return
            elif r == "J":
                print("Deleting redundant entrys...")
                r = False
        for id in tqdm(delete):
            sql = "DELETE FROM `{}` WHERE `{}`.`ID` = {}".format(db, db, id)
            self.cursor.execute(sql)
            self.db.commit()

    def split(self, *args, **kwargs):
        print("Start splitting files:")
        r = True
        while r:
            r = input("Directory: ")
            if r == "exit":
                return
            else:
                directory = r[r.find("Browser"):]
                r = False
        r = True
        while r:
            r = input("Size: ")
            if r == "exit":
                return
            else:
                try:
                    size = int(r)
                    r = False
                except ValueError:
                    pass
        stmt_objects = "UPDATE `objects` SET PATH = `%s` WHERE PATH = %s"
        stmt_gallery = "UPDATE `gallery` SET PATH = `%s` WHERE PATH = %s"
        for index, file in enumerate(os.listdir(os.path.join(os.getcwd(), "static", directory))):
            if not os.path.isfile(os.listdir(os.path.join(os.getcwd(), "static", directory, file))):
                continue
            number = str(index // size)
            id = "".join(["0" for _ in range(2-len(number))]) + number
            name = "Neuer Ordner(%s)" % id
            if not os.path.exists(os.path.join(os.getcwd(), "static", directory, name)):
                logging.info("Creating folder %s" % name)
                os.makedirs(os.path.join(os.getcwd(), "static", directory, name))
            data = (os.path.join(directory, name, file).replace("\\", "/"), os.path.join(directory, file).replace("\\", "/"))
            shutil.move(os.path.join(os.getcwd(), "static", directory, file),
                        os.path.join(os.getcwd(), "static", directory, name, file))
            try:
                self.cursor.execute(stmt_objects, data)
                self.cursor.execute(stmt_gallery, data)
            except Exception as e:
                logging.error(e)
            
        self.db.commit()

    def sync_database(self, stmt, data):
        databases = ["objects", "content", "gallery"]
        # Objects: ID, PATH, TAGS
        # Content: ID, Path, Visited, Visits
        # Gallery: ID, PATH, FOLDER
        data = list(data)
        if data[0] == "objects":
            self.cursor.execute(stmt, data)
            data[0] = "gallery"
            self.cursor.execute(stmt, data)
            self.db.commit()
            stmt = stmt.replace("PATH", "Path")
            data[0] = "content"
            self.cursor.execute(stmt, data)
            
    def add_folder(self):
        r = True
        while r:
            r = input("Ursprungsverzeichnis: ")
            if r == "exit":
                return
            else:
                origin = r
                r = False
        r = True
        while r:
            r = input("Zielverzeichnis: ")
            if r == "exit":
                return
            else:
                target = r
                r = False
        util.move_dir(origin, target)
        """if not os.path.exists("NEW"):
            os.mkdir("NEW")
        for i in os.listdir(origin):
            shutil.move(os.path.join(origin, i), "NEW")
        utils.clear("NEW")
        shutil.move("NEW", target)
        shutil.rmtree("NEW")"""
        print("Path successfully moved.")
        
    def duplicates(self, directory=None):
        if directory is None:
            r = True
            while r:
                r = input("Directory: ")
                if r == "exit":
                    return
                else:
                    path = r
                    r = False
        else:
            path = directory
        hashs = dict()
        dels = 0
        mycursor = self.db.cursor()

        mycursor.execute("SELECT * FROM gallery")

        myresult = {i[1]: i for i in mycursor.fetchall()}
        if path.startswith("Browser"):
            path = os.path.join(os.getcwd(), "static", path)
        for r, _, f in os.walk(path):
            for file in tqdm(f):
                if not file.endswith(".jpg"):
                    continue
                try:
                    image_org = Image.open(os.path.join(r, file))
                except PIL.UnidentifiedImageError:
                    continue
                image_org = image_org.resize((250, 250))
                mean = ImageStat.Stat(image_org).mean
                image_org.close()
                mean = str(mean)
                if mean in hashs.keys():
                    rel = os.path.join(r, file)
                    path = os.path.join(r, file)
                    rel = rel[rel.find("Browser"):]
                    rel = rel.replace("\\", "/")
                    if rel in myresult.keys():
                        yet = hashs[mean]
                        yet = yet[yet.find("Browser"):]
                        yet = yet.replace("\\", "/")
                        if yet in myresult.keys():
                            mycursor.execute("DELETE FROM gallery WHERE PATH = %s", (rel, ))
                            mycursor.execute("DELETE FROM objects WHERE PATH = %s", (rel, ))
                        else:
                            mycursor.execute("DELETE FROM objects WHERE PATH = %s", (yet, ))
                            path, hashs[mean] = hashs[mean], path
                    else:
                        if os.path.getsize(os.path.join(r, file)) < os.path.getsize(hashs[mean]):
                            mycursor.execute("DELETE FROM objects WHERE PATH = %s", (rel, ))
                        else:
                            yet = hashs[mean]
                            yet = yet[yet.find("Browser"):]
                            yet = yet.replace("\\", "/")
                            mycursor.execute("DELETE FROM objects WHERE PATH = %s", (yet, ))  
                            path, hashs[mean] = hashs[mean], path                          
                    self.db.commit()
                    os.remove(path)
                    dels += 1
                else:
                    hashs[mean] = os.path.join(r, file)
        print("Deleted {} elements.".format(dels))

    def get_id(self):
        needle = "Browser/NEW/Test/0303614.jpg"
        column = "PATH"
        table = "gallery"
        print(util.get_id(column, needle, table))

        
if __name__ == "__main__":
    t = TERMINAL()
    t.run()
