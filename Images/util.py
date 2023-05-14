from settings import SETTINGS
import os
import shutil
import logging
logging.getLogger('PIL').setLevel(logging.WARNING)
from PIL import Image, ImageStat
import PIL
import concurrent.futures
logging.basicConfig(filename='browser.log', filemode="a", level=logging.DEBUG)

import mysql.connector

db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="browser"
)

def clear(path, rename=True,):
        '''Prepares downloaded files for sorting'''
        #Clear images with size, dimensions and file-type
        number = SETTINGS.load_settings()["id_number"]
        for r,d,f in os.walk(path):
            for files in f:
                if os.path.splitext(files)[1] not in SETTINGS.load_settings()["allowed_file_types"]:
                    logging.info("Deleting file {} because of wrong extension".format(files))
                    os.remove(os.path.join(r, files))
                else:
                    file_path = os.path.join(r,files)
                    ext = os.path.splitext(files)[1]
                    if rename:
                        new_path = os.path.join(r, name(number,7) + ext)
                        number += 1
                    else:
                        new_path = os.path.join(r, os.path.splitext(files)[0]+".temp")
                        os.rename(file_path, new_path)
                        file_path = os.path.join(r, os.path.splitext(files)[0]+".temp")
                        new_path = os.path.join(r, os.path.splitext(files)[0]+ext)
                    os.rename(file_path, new_path)
        sets = SETTINGS.load_settings()
        sets["id_number"] = number
        logging.info("Writing settings")
        SETTINGS.write_settings(sets)

def copy(start, end):
    for i in os.listdir(start):
        shutil.copy(start + "/" + i, end)

def move(start, end):
    futs = list()
    with concurrent.futures.ThreadPoolExecutor(max_workers=5) as executor:
        for i in os.listdir(start):
            futs.append(executor.submit(_move, start + "/" + i, end))
        concurrent.futures.as_completed(futs)

def _move(start, end):
    logging.info("Move {} to {}".format(start, end))
    shutil.move(start, end)

def name(number, places):
        #Rename a file to 'number' with 'places' digits
        new_number = str(number)
        length = len(list(new_number))
        st = ""
        for i in range(places-length):
            st += "0"
        fine = st + new_number
        return fine

def move_dir(dir, end):
    rel_end = end[end.find("Browser"):]
    if end == rel_end:
        end = os.path.join(os.getcwd(), "static", end)
    n = duplicate(end)
    clear(dir)
    logging.info("Deleted {} duplicate files".format(n))
    shutil.move(dir, end)

def update_objects(path):
    mycursor = db.cursor()
    sql = "INSERT INTO objects (path) VALUES (%s)"
    val = (path)
    mycursor.execute(sql, val)
    db.commit()

def duplicate(path):
    """Checks all files in the given directory for duplicates."""
    hashs = []
    dels = 0
    for r, _, f in os.walk(path):
        for file in f:
            if not file.endswith(".jpg"):
                continue
            try:
                image_org = Image.open(os.path.join(r, file))
            except PIL.UnidentifiedImageError:
                os.remove(os.path.join(r, file))
                continue
            mean = ImageStat.Stat(image_org).mean
            image_org.close()
            if mean in hashs:
                os.remove(os.path.join(r, file))
                dels += 1
            else:
                hashs.append(mean)
    return dels            

def check_duplicates(path):
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="browser"
        )
        hashs = dict()
        dels = 0
        mycursor = db.cursor()

        mycursor.execute("SELECT * FROM gallery")

        myresult = {i[1]: i for i in mycursor.fetchall()}
        if path.startswith("Browser"):
            path = os.path.join(os.getcwd(), "static", path)
        for r, _, f in os.walk(path):
            for file in f:
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
                    db.commit()
                    os.remove(path)
                    dels += 1
                else:
                    hashs[mean] = os.path.join(r, file)
        return dels

def get_id(column, needle, table):
    sql = "SELECT * FROM {} WHERE {} = '{}'".format(table, column, needle)
    cursor = db.cursor()
    cursor.execute(sql)
    try:
        return cursor.fetchall()[0][0]
    except IndexError:
        raise IndexError("Can't find ID for '{}' in column '{}' and table '{}'".format(needle, column, table))

def unix_dir(d):
    d = d.replace("\\", "/")
    d = d.replace("/Images", "")
    d = d.replace("/ Browser", "/Browser")
    return d.replace("//", "/")

def get_table(table):
    cursor = db.cursor()
    cursor.execute("SELECT * FROM {}".format(table))
    return cursor.fetchall()

def renamed_object(old, new):
    old = old.split("/")
    new = new.split("/")
    assert len(old) > len(new), "Paths must have equal length!"
    i = 0
    for index, comp in enumerate(old):
        if new[index] != comp:
            return (i, i+len(comp), new[index])
        i += len(comp) + 1

def rename(path, new_path):
    if unix_dir(path) == unix_dir(path[path.find("Browser"):]):
        path = unix_dir(os.path.join(os.getcwd(), "static", path))
    logging.info("Renaming directory '{}' to '{}'".format(path, new_path))
    cursor = db.cursor()
    for r, d, f in os.walk(path):
        r = unix_dir(r)
        logging.info("Renaming directory '{}'".format(r))
        # Actualize files
        for file in map(unix_dir, [os.path.join(r, i) for i in f]):
            di = get_id("PATH", file[file.find("Browser"):], "objects")
            old = file[file.find("Browser"):]
            new = new_path[new_path.find("Browser"):]
            change = renamed_object(old, new)
            # logging.info("Change-Info: {} -> {}".format(change, old[:change[0]] + change[2]  + old[change[1]:]))
            rel = old[:change[0]] + change[2]  + old[change[1]:]
            try:
                di2 = get_id("PATH", file[file.find("Browser"):], "gallery")
                sql = "UPDATE gallery SET PATH = %s WHERE ID = %s"
                cursor.execute(sql, (rel, di2))
                logging.info("(gallery) Rename '{}' to '{}'".format(old, rel))
            except IndexError:
                pass
            try:
                sql = "UPDATE objects SET PATH = %s WHERE ID = %s"
                cursor.execute(sql, (rel, di))
            except Exception as e:
                logging.error(e)
            logging.info("(objects) Rename '{}' to '{}'".format(old, rel))
        # Actualize dirs
        for directory in map(unix_dir, [os.path.join(r, i) for i in d]):
            di = get_id("Path", directory[directory.find("Browser"):], "content")
            old = directory[directory.find("Browser"):]
            new = new_path[new_path.find("Browser"):]
            change = renamed_object(old, new)
            rel = old[:change[0]] + change[2]  + old[change[1]:]
            try:
                sql = "UPDATE objects SET PATH = %s WHERE ID = %s"
                cursor.execute(sql, (rel, di))
            except Exception as e:
                logging.error(e)
            logging.info("(content) Rename '{}' to '{}'".format(old, rel))
    db.commit()

def delete(path):
    rel_path = path[path.find("Browser"):]
    cursor = db.cursor()
    if rel_path == path:
        path = unix_dir(os.path.join(os.getcwd(), "static", path))
    if not os.path.isfile(path):
        try:
            di = get_id("Path", rel_path, "content")
            sql = "DELETE FROM `content` WHERE `content`.`ID` = {}".format(di)
            cursor.execute(sql)
        except IndexError:
            pass
        db.commit()
        return
    try:
        di = get_id("PATH", rel_path, "gallery")
        sql = "DELETE FROM `gallery` WHERE `gallery`.`ID` = {}".format(di)
        cursor.execute(sql)
    except IndexError:
        pass
    try:
        di = get_id("PATH", rel_path, "objects")
        sql = "DELETE FROM `objects` WHERE `objects`.`ID` = {}".format(di)
        cursor.execute(sql)
        logging.info("Successfully deleted file {}".format(rel_path))
    except IndexError:
        pass
    db.commit()

